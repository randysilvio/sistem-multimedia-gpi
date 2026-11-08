<?php

namespace Tests\Feature;

use App\Models\Liturgy;
use App\Models\Schedule;
use App\Models\ScheduleDetail;
use App\Models\Song;
use App\Models\User;
use App\Services\SlideBuilder;
use Database\Seeders\LaguIbadahMingguSeeder;
use Database\Seeders\TemplateIbadahMingguSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TemplateIbadahMingguTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeders_create_five_templates_and_complete_songs_idempotently(): void
    {
        $this->seed([TemplateIbadahMingguSeeder::class, LaguIbadahMingguSeeder::class]);
        $this->seed([TemplateIbadahMingguSeeder::class, LaguIbadahMingguSeeder::class]);

        foreach (TemplateIbadahMingguSeeder::NAMES as $name) {
            $this->assertSame(1, Liturgy::where('name', $name)->count(), $name);
            $liturgy = Liturgy::where('name', $name)->first();
            $titles = $liturgy->items->pluck('title')->all();

            $this->assertContains('Votum dan Salam', $titles, $name);
            $this->assertContains('Bacaan Alkitab', $titles, $name);
            $this->assertGreaterThanOrEqual(6, collect($titles)->filter(fn ($t) => str_starts_with($t, 'Nyanyian'))->count(), $name);

            foreach ($liturgy->items as $item) {
                // Template hanya struktur & kalimat liturgi: slot nyanyian tidak berisi lirik
                if (SlideBuilder::itemType($item->title) === 'nyanyian') {
                    $this->assertEmpty($item->static_content, "$name: {$item->title} tidak boleh berisi lirik");
                }
                // "Doa Pembacaan Alkitab" adalah teks doa, bukan slot ayat
                if (str_starts_with($item->title, 'Doa')) {
                    $this->assertNotSame('alkitab', SlideBuilder::itemType($item->title));
                }
            }
        }

        $minggu3 = Liturgy::where('name', TemplateIbadahMingguSeeder::NAMES['III'])->first()->items->pluck('title');
        $this->assertContains('Pengakuan Iman Atanasius', $minggu3);
        $this->assertContains('Pengakuan Iman Rasuli', Liturgy::where('name', TemplateIbadahMingguSeeder::NAMES['I'])->first()->items->pluck('title'));

        // Semua lagu dari kelima slide ada, dengan bait LENGKAP
        foreach (['KJ' => ['21', '14', '27', '29', '49', '50', '348', '356', '393', '425', '450', '452'],
                  'NKB' => ['2', '5', '119', '141', '189', '208', '210', '213'],
                  'PKJ' => ['2', '13', '15', '43', '145', '183', '274', '290'],
                  'NR' => ['3', '5', '79', '132', '154', '165', '189'],
                  'DSL' => ['118', '186']] as $book => $numbers) {
            foreach ($numbers as $n) {
                $this->assertTrue(Song::where('book', $book)->where('number', $n)->exists(), "$book $n");
            }
        }
        $this->assertSame([1, 2, 3, 4, 5, 6, 7], array_keys(Song::where('book', 'NR')->where('number', '5')->first()->numberedVerses()));
        $this->assertCount(7, Song::where('book', 'KJ')->where('number', '49')->first()->verses);
        $this->assertNotEmpty(Song::where('book', 'KJ')->where('number', '450')->first()->chorus);
    }

    public function test_november_2026_templates_and_songs(): void
    {
        $this->seed([TemplateIbadahMingguSeeder::class, LaguIbadahMingguSeeder::class]);

        $titles = fn (string $code) => Liturgy::where('name', TemplateIbadahMingguSeeder::NAMES[$code])->first()->items->pluck('title')->all();

        $this->assertContains('Nyanyian Doa Pembacaan Alkitab', $titles('IV'));
        $this->assertSame('nyanyian', SlideBuilder::itemType('Nyanyian Doa Pembacaan Alkitab'));
        $this->assertContains('Nyanyian Penyesalan Anak', $titles('ANAK'));
        $this->assertContains('Panggilan Beribadah', $titles('ADVEN'));
        $this->assertContains('Nyanyian Doa Epiklese', $titles('ADVEN'));
        // Minggu III dilengkapi dari tata ibadah 15 November
        $this->assertContains('Amanat Pengutusan', $titles('III'));
        $this->assertContains('Ajakan Memberi', $titles('III'));

        foreach (['KJ' => ['3', '26', '28', '56', '81', '85', '184', '280', '302', '346', '424'],
                  'PKJ' => ['7', '11', '42', '146', '177', '255', '292'],
                  'NKB' => ['201'], 'DSL' => ['7', '109', '112', '136', '139', '181', '188'],
                  'GPM' => ['1', '65'], 'BEBAS' => ['1', '2', '3']] as $book => $numbers) {
            foreach ($numbers as $n) {
                $this->assertTrue(Song::where('book', $book)->where('number', $n)->exists(), "$book $n");
            }
        }
        $this->assertCount(10, Song::where('book', 'KJ')->where('number', '85')->first()->verses);
        $this->assertSame([1, 3], array_keys(Song::where('book', 'DSL')->where('number', '188')->first()->numberedVerses()));
    }

    public function test_template_iii_is_upgraded_only_when_unused(): void
    {
        $old = Liturgy::create(['name' => TemplateIbadahMingguSeeder::NAMES['III']]);
        $old->items()->create(['title' => 'Bacaan Alkitab', 'is_dynamic' => true, 'order_number' => 1]);
        $other = Liturgy::create(['name' => TemplateIbadahMingguSeeder::NAMES['I']]);

        $this->seed(TemplateIbadahMingguSeeder::class);
        $this->assertContains('Amanat Pengutusan', $old->fresh()->items->pluck('title'));
        $this->assertSame(0, $other->items()->count(), 'template lain yang sudah ada tidak diubah');

        // Template yang sudah dipakai jadwal tidak disentuh
        $old->items()->delete();
        $old->items()->create(['title' => 'Bacaan Alkitab', 'is_dynamic' => true, 'order_number' => 1]);
        Schedule::create(['liturgy_id' => $old->id, 'worship_date' => '2026-11-15']);
        $this->seed(TemplateIbadahMingguSeeder::class);
        $this->assertSame(['Bacaan Alkitab'], $old->fresh()->items->pluck('title')->all());
    }

    public function test_untitled_songs_can_be_pulled_by_title(): void
    {
        $this->seed(LaguIbadahMingguSeeder::class);
        $user = User::factory()->create();

        $data = $this->actingAs($user)->getJson(route('api.lagu', ['buku' => 'BEBAS', 'nomor' => 'seka']))->json();
        $this->assertTrue($data['success']);
        $this->assertSame('Seka-Seka Yo', $data['judul']);
        $this->assertSame(['1', 'Reff'], $data['labels']);
        $this->assertSame('GPM 1 - Akang Manis Lawang', $this->actingAs($user)->getJson(route('api.lagu', ['buku' => 'GPM', 'nomor' => '1']))->json('judul'));
    }

    public function test_song_seeder_completes_partial_songs_but_keeps_longer_ones(): void
    {
        Song::create(['book' => 'NR', 'number' => '5', 'title' => 'Lama', 'verses' => ['bait 1', 'bait 7'], 'verse_numbers' => [1, 7]]);
        Song::create(['book' => 'KJ', 'number' => '21', 'title' => 'Diubah', 'verses' => ['a', 'b', 'c', 'd'], 'chorus' => 'r']);

        $this->seed(LaguIbadahMingguSeeder::class);

        $nr5 = Song::where('book', 'NR')->where('number', '5')->first();
        $this->assertCount(7, $nr5->verses);
        $this->assertNull($nr5->verse_numbers);
        $this->assertSame('Lama', $nr5->title, 'judul buatan pengguna tidak diganti');
        $this->assertSame(['a', 'b', 'c', 'd'], Song::where('book', 'KJ')->where('number', '21')->first()->verses);
    }

    public function test_long_creed_is_cut_at_sentences_into_even_pieces(): void
    {
        $this->seed(TemplateIbadahMingguSeeder::class);
        $item = Liturgy::where('name', TemplateIbadahMingguSeeder::NAMES['III'])->first()
            ->items->firstWhere('title', 'Pengakuan Iman Atanasius');
        $pieces = explode('===SLIDE_BREAK===', $item->static_content);

        $this->assertGreaterThan(15, count($pieces));
        foreach ($pieces as $p) {
            $p = trim($p);
            $this->assertMatchesRegularExpression('/[.;]$/u', $p, 'setiap slide berakhir di akhir kalimat');
            $this->assertGreaterThanOrEqual(60, mb_strlen($p), "potongan yatim: $p");
            $this->assertLessThanOrEqual(200, mb_strlen($p));
        }
    }

    public function test_legacy_template_is_renamed_to_minggu_v(): void
    {
        $legacy = Liturgy::create(['name' => TemplateIbadahMingguSeeder::LEGACY_NAME]);
        $this->seed(TemplateIbadahMingguSeeder::class);

        $this->assertSame(TemplateIbadahMingguSeeder::NAMES['V'], $legacy->fresh()->name);
        $this->assertSame(1, Liturgy::where('name', TemplateIbadahMingguSeeder::NAMES['V'])->count());
    }

    public function test_song_api_returns_real_verse_numbers(): void
    {
        Song::create(['book' => 'NR', 'number' => '165', 'title' => 'Yesus Memesan'] + Song::normalizeVerses(['bait satu', 'bait tiga'], [1, 3]));
        $user = User::factory()->create();

        $data = $this->actingAs($user)->getJson(route('api.lagu', ['buku' => 'NR', 'nomor' => '165']))->assertOk()->json();
        $this->assertTrue($data['success']);
        $this->assertSame(['1', '3'], $data['labels']);
        $this->assertCount(2, explode('===SLIDE_BREAK===', $data['text']));
    }

    public function test_song_form_keeps_custom_verse_numbers(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->post(route('songs.store'), [
            'book' => 'NR', 'number' => '999', 'title' => 'Uji',
            'verses' => ['bait satu', 'bait tujuh', ''],
            'verse_numbers' => ['1', '7', '8'],
        ])->assertRedirect();

        $song = Song::where('number', '999')->first();
        $this->assertSame([1 => 'bait satu', 7 => 'bait tujuh'], $song->numberedVerses());

        $this->actingAs($user)->post(route('songs.store'), [
            'book' => 'NR', 'number' => '998', 'title' => 'Urut', 'verses' => ['a', 'b'], 'verse_numbers' => ['1', '2'],
        ]);
        $this->assertNull(Song::where('number', '998')->first()->verse_numbers, 'nomor berurutan tidak perlu disimpan');
    }

    public function test_sung_responses_are_marked_as_song_on_the_live_screen(): void
    {
        $liturgy = Liturgy::create(['name' => 'Uji']);
        $item = $liturgy->items()->create(['title' => 'Gloria Besar', 'is_dynamic' => true, 'order_number' => 1]);
        $schedule = Schedule::create(['liturgy_id' => $liturgy->id, 'worship_date' => '2026-11-08']);
        ScheduleDetail::create(['schedule_id' => $schedule->id, 'liturgy_item_id' => $item->id, 'dynamic_content' => [
            'custom_title' => 'Gloria Besar',
            'content' => "[PF]\nMarilah kita bernyanyi\n===SLIDE_BREAK===\n[JEMAAT MENYANYIKAN]\nKemuliaan bagi Allah",
        ]]);

        $slides = collect(app(SlideBuilder::class)->build($schedule))->where('section', 'Gloria Besar')->values();

        $this->assertFalse($slides[0]['sung'], 'kalimat liturgi biasa bukan nyanyian');
        $this->assertTrue($slides[1]['sung']);
    }

    public function test_speaker_marker_becomes_label_on_every_split_slide(): void
    {
        $liturgy = Liturgy::create(['name' => 'Uji']);
        $item = $liturgy->items()->create(['title' => 'Pengakuan Dosa', 'is_dynamic' => true, 'order_number' => 1]);
        $schedule = Schedule::create(['liturgy_id' => $liturgy->id, 'worship_date' => '2026-11-08']);
        $long = str_repeat('Sebagai persekutuan keluarga kami tidak menghadirkan kasih-Mu. ', 6);
        ScheduleDetail::create(['schedule_id' => $schedule->id, 'liturgy_item_id' => $item->id, 'dynamic_content' => [
            'custom_title' => 'Pengakuan Dosa',
            'content' => "[PNT]\n$long\n===SLIDE_BREAK===\n[JEMAAT]\nTuhan kasihanilah kami",
        ]]);

        $slides = collect(app(SlideBuilder::class)->build($schedule))->where('section', 'Pengakuan Dosa')->values();

        $this->assertGreaterThan(2, $slides->count());
        $this->assertSame('JEMAAT', $slides->last()['speaker']);
        $this->assertSame('Tuhan kasihanilah kami', $slides->last()['content']);
        foreach ($slides->slice(0, -1) as $s) {
            $this->assertSame('PNT', $s['speaker']);
            $this->assertStringNotContainsString('[PNT]', $s['content']);
        }
    }

    public function test_long_pieces_are_split_evenly_without_orphan_lines(): void
    {
        $builder = app(SlideBuilder::class);

        // 4 baris berakhiran tanda baca tetap SATU slide (dulu dipecah 2+2)
        $this->assertCount(1, $builder->splitText("Hormat bagi Allah Bapa,\nhormat bagi Anak-Nya,\nhormat bagi Roh Penghibur,\nketiganya Yang Esa."));

        // 5 baris -> 2+3 di akhir kalimat, bukan 4+1 ("Bersyukurlah!" sendirian)
        $pieces = $builder->splitText("Mulia, mulia namaNya.\nBagi Yesus kemuliaan, puji, sembah!\nMulia, kekuasaanNya\nMemb'ri berkat bagi jemaat,\nBersyukurlah!");
        $this->assertSame(["Mulia, mulia namaNya.\nBagi Yesus kemuliaan, puji, sembah!", "Mulia, kekuasaanNya\nMemb'ri berkat bagi jemaat,\nBersyukurlah!"], $pieces);

        foreach ($builder->splitText(str_repeat('Kami telah memberontak dan mungkir terhadap Tuhan. ', 5)) as $p) {
            $this->assertGreaterThan(40, mb_strlen($p), 'tidak ada potongan yatim');
        }
    }

    public function test_instruction_slides_get_a_meaningful_section_name(): void
    {
        $liturgy = Liturgy::create(['name' => 'Uji']);
        $item = $liturgy->items()->create(['title' => 'Slide Bebas', 'is_dynamic' => true, 'order_number' => 1]);
        $schedule = Schedule::create(['liturgy_id' => $liturgy->id, 'worship_date' => '2026-11-08']);
        ScheduleDetail::create(['schedule_id' => $schedule->id, 'liturgy_item_id' => $item->id, 'dynamic_content' => ['custom_title' => '', 'content' => "JEMAAT BERDIRI\nPENGUTUSAN DAN BERKAT"]]);

        $slide = collect(app(SlideBuilder::class)->build($schedule))->firstWhere('type', 'instruksi');
        $this->assertSame('Jemaat Berdiri', $slide['section']);
    }
}
