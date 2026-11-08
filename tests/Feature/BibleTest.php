<?php

namespace Tests\Feature;

use App\Models\Liturgy;
use App\Models\Schedule;
use App\Models\ScheduleDetail;
use App\Models\User;
use App\Services\Bible;
use App\Services\SlideBuilder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;
use Tests\TestCase;

class BibleTest extends TestCase
{
    use RefreshDatabase;

    /** Jawaban API SABDA tiruan: satu pasal berisi $count ayat "Teks c:v." */
    private function fakeSabda(int $bookId, string $bookName, array $chapters): void
    {
        Http::fake(function ($request) use ($bookId, $bookName, $chapters) {
            parse_str(parse_url($request->url(), PHP_URL_QUERY), $q);
            preg_match('/(\d+)$/', $q['passage'], $m);
            $c = (int) $m[1];
            $verses = '';
            for ($v = 1; $v <= ($chapters[$c] ?? 0); $v++) {
                $text = $chapters["$c:$v"] ?? "Teks $c:$v.";
                $verses .= "<verse><number>$v</number><text>$text</text></verse>";
            }

            return Http::response("<?xml version=\"1.0\"?><bible><book name=\"$bookName\"><book_id>$bookId</book_id><chapter><chap>$c</chap><verses>$verses</verses></chapter></book></bible>");
        });
    }

    public function test_reference_parser_understands_common_indonesian_formats(): void
    {
        $b = new Bible();
        $cases = [
            'Yohanes 3:16-18' => 'Yohanes 3:16-18',
            'yoh. 3 : 16 - 18' => 'Yohanes 3:16-18',
            'NEHEMIA 8 : 1 - 19' => 'Nehemia 8:1-19',
            '1 Yoh 1:9' => '1 Yohanes 1:9',
            'I Yohanes 1:9' => '1 Yohanes 1:9',
            'II Kor 5:17' => '2 Korintus 5:17',
            'Imamat 19:2' => 'Imamat 19:2',
            'Mzm 23' => 'Mazmur 23',
            'Yoh 3:16,18,20-22' => 'Yohanes 3:16, 18, 20-22',
            'Yoh 3:36–4:2' => 'Yohanes 3:36-4:2',
            'Yes 45:1-8; Mat 5:1-3' => 'Yesaya 45:1-8; Matius 5:1-3',
            'Yudas 3' => 'Yudas 1:3',
            'Yohanis 3:16' => 'Yohanes 3:16',
            'Kis 2:1-4' => 'Kisah Para Rasul 2:1-4',
            '2 Tawarikh 34 : 14 - 33' => '2 Tawarikh 34:14-33',
            'Efesus 2 : 1 - 10' => 'Efesus 2:1-10',
        ];
        foreach ($cases as $in => $out) {
            $this->assertSame($out, $b->format($b->parse($in)), $in);
        }
    }

    public function test_reference_parser_gives_helpful_errors(): void
    {
        $b = new Bible();
        foreach (['Yo 3:16' => 'kurang jelas', 'Xyz 3:16' => 'tidak dikenal', 'Yoh 30:1' => 'sampai pasal 21',
                  'Wahyu' => 'pasalnya', 'Yoh 3:18-16' => 'terbalik', '' => 'Ketik referensi'] as $in => $msg) {
            try {
                $b->parse($in);
                $this->fail("\"$in\" seharusnya ditolak");
            } catch (InvalidArgumentException $e) {
                $this->assertStringContainsString($msg, $e->getMessage(), $in);
            }
        }
    }

    public function test_passage_fetches_tb_per_chapter_caches_it_and_trims_overlong_ranges(): void
    {
        Cache::flush();
        $this->fakeSabda(43, 'Yohanes', [3 => 36, 4 => 54, '3:16' => 'Karena begitu besar kasih Allah akan dunia ini.']);
        $b = new Bible();

        $p = $b->passage('Yoh 3:16-17');
        $this->assertSame('Yohanes 3:16-17', $p['reference']);
        $this->assertSame("16 Karena begitu besar kasih Allah akan dunia ini.\n17 Teks 3:17.", $p['text']);

        // Melewati akhir pasal: referensi dirapikan
        $this->assertSame('Yohanes 3:35-36', $b->passage('Yoh 3:35-40')['reference']);

        // Lintas pasal: label "pasal:ayat"
        $this->assertStringContainsString("3:36 Teks 3:36.\n4:1 Teks 4:1.", $b->passage('Yoh 3:36-4:2')['text']);

        // Pasal sudah tersimpan: tetap jalan walau server mati
        Http::fake(fn () => Http::response('', 500));
        $this->assertSame('Yohanes 3:16', $b->passage('Yoh 3:16')['reference']);

        try {
            $b->passage('Yoh 3:50');
            $this->fail('Ayat di luar pasal harus ditolak');
        } catch (InvalidArgumentException $e) {
            $this->assertStringContainsString('hanya sampai ayat 36', $e->getMessage());
        }
    }

    public function test_sabda_substituting_a_wrong_book_is_not_accepted(): void
    {
        Cache::flush();
        $this->fakeSabda(1, 'Kejadian', [1 => 31]); // server menjawab Kejadian untuk permintaan apa pun
        $this->expectException(\RuntimeException::class);
        (new Bible())->chapter(43, 1);
    }

    public function test_api_endpoint_returns_reference_and_text(): void
    {
        Cache::flush();
        $this->fakeSabda(19, 'Mazmur', [23 => 6]);
        $user = User::factory()->create();

        $this->actingAs($user)->getJson(route('api.alkitab', ['q' => 'mzm 23 : 1 - 2']))
            ->assertOk()
            ->assertJson(['success' => true, 'reference' => 'Mazmur 23:1-2', 'version' => 'TB', 'count' => 2, 'text' => "1 Teks 23:1.\n2 Teks 23:2."]);

        $this->actingAs($user)->getJson(route('api.alkitab', ['q' => 'Xyz 1:1']))
            ->assertOk()->assertJson(['success' => false]);
    }

    private function scheduleWithBible(array $content): Schedule
    {
        $liturgy = Liturgy::create(['name' => 'Uji']);
        $doa = $liturgy->items()->create(['title' => 'Doa Pembacaan Alkitab', 'is_dynamic' => true, 'order_number' => 1, 'static_content' => '[PF]\nYa Tuhan']);
        $item = $liturgy->items()->create(['title' => 'Bacaan Alkitab', 'is_dynamic' => true, 'order_number' => 2]);
        $schedule = Schedule::create(['liturgy_id' => $liturgy->id, 'worship_date' => '2026-11-08']);
        ScheduleDetail::create(['schedule_id' => $schedule->id, 'liturgy_item_id' => $item->id, 'dynamic_content' => $content]);

        return $schedule;
    }

    public function test_bible_slides_show_reference_then_whole_verses_with_local_references(): void
    {
        $long = str_repeat('Firman Tuhan itu benar, ', 14) . 'dan kekal selamanya.';
        $schedule = $this->scheduleWithBible([
            'ref' => 'yes 45:1-4',
            'content' => "1 Beginilah firman TUHAN.\n2 Aku sendiri hendak berjalan di depanmu.\n3 Aku akan memberikan kepadamu harta benda yang terpendam.\n4 $long",
        ]);

        $slides = collect(app(SlideBuilder::class)->build($schedule))->where('section', 'Bacaan Alkitab')->values();

        $this->assertSame('bible_ref', $slides[0]['type']);
        $this->assertSame('Yesaya 45:1-4', $slides[0]['content']);

        $verseSlides = $slides->where('type', 'bible')->values();
        $this->assertSame('Yesaya 45:1-3', $verseSlides[0]['ref']);
        $this->assertSame(['1', '2', '3'], array_column($verseSlides[0]['verses'], 'n'));
        // Ayat panjang dipecah; nomor ayat hanya di potongan pertama
        $this->assertGreaterThanOrEqual(2, $verseSlides->where('ref', 'Yesaya 45:4')->count());
        $this->assertSame('4', $verseSlides[1]['verses'][0]['n']);
        $this->assertSame('', $verseSlides->last()['verses'][0]['n']);
        foreach ($verseSlides as $s) {
            $this->assertLessThanOrEqual(SlideBuilder::BIBLE_CHARS_PER_SLIDE + 10, mb_strlen(implode(' ', array_column($s['verses'], 'text'))));
        }
    }

    public function test_reference_only_and_legacy_format(): void
    {
        $onlyRef = collect(app(SlideBuilder::class)->build($this->scheduleWithBible(['ref' => 'Nehemia 8 : 1 - 19', 'content' => ''])));
        $this->assertSame(['Nehemia 8:1-19'], $onlyRef->where('section', 'Bacaan Alkitab')->pluck('content')->all());

        $legacy = collect(app(SlideBuilder::class)->build($this->scheduleWithBible([
            'content' => "YOHANES 3:16-17\n===SLIDE_BREAK===\n16. Karena begitu besar kasih Allah.\n17. Sebab Allah mengutus Anak-Nya.",
        ])))->where('section', 'Bacaan Alkitab')->values();
        $this->assertSame('Yohanes 3:16-17', $legacy[0]['content']);
        $this->assertSame('bible', $legacy[1]['type']);
        $this->assertSame('Yohanes 3:16-17', $legacy[1]['ref']);
        $this->assertSame(['16', '17'], array_column($legacy[1]['verses'], 'n'));
    }

    public function test_doa_pembacaan_alkitab_is_text_not_a_bible_slot(): void
    {
        $this->assertSame('teks', SlideBuilder::itemType('Doa Pembacaan Alkitab'));
        $this->assertSame('alkitab', SlideBuilder::itemType('Bacaan Alkitab'));
        $this->assertSame('alkitab', SlideBuilder::itemType('Bacaan Alkitab: Pelayanan Firman'));
        $this->assertSame('nyanyian', SlideBuilder::itemType('Nyanyian Persiapan Firman'));
        $this->assertSame('teks', SlideBuilder::itemType('Gloria Besar'));
    }

    public function test_control_panel_saves_bible_reference(): void
    {
        $schedule = $this->scheduleWithBible(['ref' => '', 'content' => '']);
        $item = $schedule->liturgy->items->firstWhere('title', 'Bacaan Alkitab');
        $user = User::factory()->create();

        $html = $this->actingAs($user)->get(route('liturgy.control', $schedule))->assertOk()->getContent();
        $this->assertStringContainsString("dynamic_content[{$item->id}][ref]", $html);

        $data = $this->actingAs($user)->postJson(route('liturgy.update', $schedule), [
            'dynamic_content' => [$item->id => ['ref' => 'Mat 5:3', 'content' => '3 Berbahagialah orang yang miskin di hadapan Allah.']],
        ])->assertOk()->json();

        $bible = collect($data['slides'])->firstWhere('type', 'bible');
        $this->assertSame('Matius 5:3', $bible['ref']);
    }
}
