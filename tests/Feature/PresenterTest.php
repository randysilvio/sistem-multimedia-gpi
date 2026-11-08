<?php

namespace Tests\Feature;

use App\Models\Liturgy;
use App\Models\Schedule;
use App\Models\ScheduleCustomSlide;
use App\Models\ScheduleDetail;
use App\Models\Song;
use App\Models\User;
use App\Services\DisplaySettings;
use App\Services\SlideBuilder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PresenterTest extends TestCase
{
    use RefreshDatabase;

    private function makeSchedule(): Schedule
    {
        $liturgy = Liturgy::create(['name' => 'Ibadah Minggu']);
        $song = $liturgy->items()->create(['title' => 'Nyanyian: Pujian Pembuka', 'is_dynamic' => true, 'order_number' => 1]);
        $bible = $liturgy->items()->create(['title' => 'Bacaan Alkitab', 'is_dynamic' => true, 'order_number' => 2]);
        $instr = $liturgy->items()->create(['title' => 'Slide Bebas', 'is_dynamic' => true, 'order_number' => 3]);

        $schedule = Schedule::create([
            'liturgy_id' => $liturgy->id,
            'worship_date' => '2026-11-08',
            'theme' => 'Kasih',
            'preacher_name' => 'Pdt. A',
            'theme_color' => '#123456',
        ]);

        ScheduleDetail::create(['schedule_id' => $schedule->id, 'liturgy_item_id' => $song->id, 'dynamic_content' => [
            'judul' => 'KJ 1 - Haleluya',
            'bait' => ['1' => "Baris satu\nBaris dua", 'ref_a' => "[REFF] Reff satu\nReff dua"],
            'use_camera' => false,
        ]]);

        $verses = [];
        for ($v = 1; $v <= 12; $v++) {
            $verses[] = "$v. Ini adalah ayat nomor $v yang cukup panjang untuk dipecah menjadi beberapa baris.";
        }
        ScheduleDetail::create(['schedule_id' => $schedule->id, 'liturgy_item_id' => $bible->id, 'dynamic_content' => [
            'content' => "YOHANES 3:1-12\n===SLIDE_BREAK===\n" . implode("\n", $verses),
            'use_camera' => true,
        ]]);

        ScheduleDetail::create(['schedule_id' => $schedule->id, 'liturgy_item_id' => $instr->id, 'dynamic_content' => [
            'custom_title' => '',
            'content' => 'Jemaat berdiri',
        ]]);

        ScheduleCustomSlide::create(['schedule_id' => $schedule->id, 'liturgy_item_id' => $instr->id, 'title' => 'Info', 'content' => 'Persembahan[KAMERA_AKTIF]']);

        return $schedule;
    }

    public function test_builder_produces_ordered_slides_with_unique_stable_keys(): void
    {
        $schedule = $this->makeSchedule();
        $slides = app(SlideBuilder::class)->build($schedule, []);

        $types = array_column($slides, 'type');
        $this->assertSame('cover', $types[0]);
        $this->assertSame('closing', end($types));
        $this->assertContains('song_cover', $types);
        $this->assertContains('instruksi', $types);

        $keys = array_column($slides, 'key');
        $this->assertSame(count($keys), count(array_unique($keys)), 'slide keys must be unique');

        $again = array_column(app(SlideBuilder::class)->build($schedule->fresh(), []), 'key');
        $this->assertSame($keys, $again, 'slide keys must be stable between builds');

        $reff = collect($slides)->firstWhere('title', 'Pujian Pembuka - KJ 1 - Haleluya (Reff)');
        $this->assertNotNull($reff);
        $this->assertSame('', $reff['watermark']);
        $this->assertStringNotContainsString('[REFF]', $reff['content']);

        $custom = collect($slides)->firstWhere('type', 'custom');
        $this->assertTrue($custom['use_camera']);
        $this->assertSame('Persembahan', $custom['content']);
    }

    public function test_manual_slide_break_pieces_are_still_split_when_too_long(): void
    {
        $slides = app(SlideBuilder::class)->build($this->makeSchedule(), []);
        $bible = array_values(array_filter($slides, fn ($s) => $s['section'] === 'Bacaan Alkitab'));

        // Format lama (referensi di potongan pertama) dirapikan menjadi slide referensi
        $this->assertSame('bible_ref', $bible[0]['type']);
        $this->assertSame('Yohanes 3:1-12', $bible[0]['content']);
        $this->assertGreaterThan(3, count($bible), 'a 12-verse passage must not be crammed into one slide');
        $numbers = [];
        foreach (array_slice($bible, 1) as $s) {
            $this->assertSame('bible', $s['type']);
            $this->assertLessThanOrEqual(SlideBuilder::BIBLE_CHARS_PER_SLIDE, mb_strlen(implode(' ', array_column($s['verses'], 'text'))) + 4 * count($s['verses']));
            $numbers = array_merge($numbers, array_column($s['verses'], 'n'));
        }
        $this->assertSame(array_map('strval', range(1, 12)), $numbers, 'setiap ayat utuh, berurutan, tanpa terpotong');
        foreach ($bible as $s) {
            $this->assertTrue($s['use_camera']);
        }
    }

    public function test_display_settings_default_to_55_inch_tv_and_are_sanitized(): void
    {
        $schedule = $this->makeSchedule();
        $s = DisplaySettings::for($schedule);

        $this->assertEqualsWithDelta(121.8, $s['screen']['widthCm'], 0.1);
        $this->assertEqualsWithDelta(68.5, $s['screen']['heightCm'], 0.1);
        $this->assertEquals(20, $s['screen']['marginTopCm']);
        $this->assertEquals(5, $s['screen']['marginSideCm']);
        $this->assertEquals(5, $s['screen']['marginBottomCm']);
        $this->assertSame('#123456', $s['design']['color1']);

        $clean = DisplaySettings::sanitize([
            'design' => ['bg' => 'evil', 'color2' => 'red;}', 'speed' => 99, 'font' => 'Comic'],
            'screen' => ['widthCm' => -5],
            'header' => ['show' => '0'],
            'fonts' => ['i1-0' => 90, 'bad key!' => 50, 'i2-0' => 9999],
            'unknown' => 'x',
        ]);
        $this->assertSame('aurora', $clean['design']['bg']);
        $this->assertSame(DisplaySettings::defaults()['design']['color2'], $clean['design']['color2']);
        $this->assertLessThanOrEqual(3, $clean['design']['speed']);
        $this->assertSame('Inter', $clean['design']['font']);
        $this->assertGreaterThan(0, $clean['screen']['widthCm']);
        $this->assertFalse($clean['header']['show']);
        $this->assertSame(['i1-0' => 90, 'i2-0' => 240], $clean['fonts']);
        $this->assertArrayNotHasKey('unknown', $clean);
    }

    public function test_pages_and_slides_endpoint_render_for_logged_in_user(): void
    {
        $user = User::factory()->create();
        $schedule = $this->makeSchedule();

        $this->actingAs($user)->get(route('liturgy.control', $schedule))->assertOk()->assertSee('PRESENTER', false);
        $this->actingAs($user)->get(route('liturgy.presentation', $schedule))->assertOk()->assertSee('PRESENTER', false);

        $this->actingAs($user)->getJson(route('presenter.slides', $schedule))
            ->assertOk()
            ->assertJsonStructure(['slides' => [['key', 'type', 'section', 'title', 'content', 'use_camera']], 'settings' => ['screen', 'design', 'header', 'fonts'], 'version']);
    }

    public function test_settings_are_saved_per_schedule_and_new_schedules_inherit_them(): void
    {
        $user = User::factory()->create();
        $schedule = $this->makeSchedule();

        $this->actingAs($user)->postJson(route('presenter.settings', $schedule), [
            'settings' => [
                'design' => ['bg' => 'waves', 'songColor' => '#ff0000', 'songMark' => false],
                'screen' => ['marginTopCm' => 18],
                'fonts' => ['cover' => 80],
                'hidden' => ['i1-0', 'bad key!', 'i1-0'],
                'notes' => ['i1-0' => '  Doa diiringi <b>instrumen</b>  ', 'i2-0' => '   ', 'bad key!' => 'x'],
            ],
        ])->assertOk()->assertJsonPath('settings.design.bg', 'waves')
            ->assertJsonPath('settings.design.songColor', '#ff0000')
            ->assertJsonPath('settings.design.songMark', false)
            ->assertJsonPath('settings.hidden', ['i1-0'])
            ->assertJsonPath('settings.notes', ['i1-0' => 'Doa diiringi instrumen']);

        $this->assertSame('waves', DisplaySettings::for($schedule->fresh())['design']['bg']);

        $this->actingAs($user)->post(route('liturgy.store'), [
            'liturgy_id' => $schedule->liturgy_id,
            'worship_date' => '2026-11-15',
            'dynamic_content' => ['x' => ''],
        ])->assertRedirect();

        $new = Schedule::latest('id')->first();
        $this->assertNotSame($schedule->id, $new->id);
        $settings = DisplaySettings::for($new);
        $this->assertSame('waves', $settings['design']['bg']);
        $this->assertEquals(18, $settings['screen']['marginTopCm']);
        $this->assertSame([], $settings['fonts'], 'per-slide font overrides are not inherited');
        $this->assertSame([], $settings['hidden'], 'hidden slides are not inherited');
        $this->assertSame([], $settings['notes'], 'operator notes are not inherited');
        $this->assertSame('#ff0000', $settings['design']['songColor'], 'song colour is a design choice and is inherited');
    }

    public function test_remote_state_roundtrip_and_not_modified(): void
    {
        $user = User::factory()->create();
        $schedule = $this->makeSchedule();

        $this->actingAs($user)->postJson(route('presenter.state.push', $schedule), ['index' => 4, 'key' => 'cover', 'blank' => 'black', 'hideText' => true])
            ->assertOk();

        $res = $this->actingAs($user)->getJson(route('presenter.state', $schedule))->assertOk()
            ->assertJsonPath('index', 4)->assertJsonPath('blank', 'black')->assertJsonPath('hideText', true);
        $seq = $res->json('seq');

        $this->actingAs($user)->getJson(route('presenter.state', $schedule) . '?seq=' . $seq)->assertNoContent();

        $this->actingAs($user)->postJson(route('presenter.state.push', $schedule), ['index' => 1, 'blank' => 'purple'])
            ->assertUnprocessable();
    }

    public function test_ajax_update_returns_fresh_slides_and_bumps_content_version(): void
    {
        $user = User::factory()->create();
        $schedule = $this->makeSchedule();
        $item = $schedule->liturgy->items->last();

        $before = $this->actingAs($user)->getJson(route('presenter.state', $schedule))->json('contentVersion');

        $res = $this->actingAs($user)->postJson(route('liturgy.update', $schedule), [
            'theme' => 'Baru',
            'dynamic_content' => [$item->id => ['custom_title' => 'Pengumuman', 'content' => 'Halo jemaat']],
        ])->assertOk()->assertJsonPath('success', true);

        $this->assertContains('Halo jemaat', array_column($res->json('slides'), 'content'));
        $after = $this->actingAs($user)->getJson(route('presenter.state', $schedule))->json('contentVersion');
        $this->assertNotEquals($before, $after);
    }

    public function test_template_in_use_cannot_be_deleted(): void
    {
        $user = User::factory()->create();
        $schedule = $this->makeSchedule();

        $this->actingAs($user)->from(route('liturgy.gallery'))
            ->delete(route('liturgy.template.destroy', $schedule->liturgy_id))
            ->assertRedirect(route('liturgy.gallery'))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('liturgies', ['id' => $schedule->liturgy_id]);
    }

    public function test_lookup_apis_require_login(): void
    {
        $this->get(route('api.lagu', ['buku' => 'KJ', 'nomor' => 1]))->assertRedirect(route('login'));
        $this->get(route('api.alkitab', ['q' => 'yoh 3:16']))->assertRedirect(route('login'));
    }

    public function test_last_minute_song_is_inserted_after_current_part_and_survives_editor_save(): void
    {
        $user = User::factory()->create();
        $schedule = $this->makeSchedule();
        Song::create(['book' => 'KJ', 'number' => '85', 'title' => 'Kusongsong Bagaimana', 'verses' => ['bait satu', 'bait dua', 'bait tiga'], 'chorus' => 'reff']);
        $bibleItem = $schedule->liturgy->items()->where('title', 'Bacaan Alkitab')->first();

        $res = $this->actingAs($user)->postJson(route('presenter.insert_song', $schedule), [
            'buku' => 'kj', 'nomor' => '85', 'bait' => [1, 3], 'after_key' => "i{$bibleItem->id}-2",
        ])->assertOk()->assertJsonPath('success', true)->assertJsonPath('judul', 'KJ 85 - Kusongsong Bagaimana');

        $slides = collect($res->json('slides'))->values();
        $cover = $slides->search(fn ($s) => $s['key'] === $res->json('coverKey'));
        $this->assertNotFalse($cover, 'sampul lagu sisipan ada di daftar slide');
        $this->assertSame('song_cover', $slides[$cover]['type']);
        $this->assertSame('Bacaan Alkitab', $slides[$cover - 1]['section'], 'disisipkan tepat setelah bagian Bacaan Alkitab');
        $lyrics = $slides->where('section', 'Nyanyian Tambahan')->where('type', 'song_lyric')->pluck('content')->values()->all();
        $this->assertSame(['bait satu', 'reff', 'bait tiga', 'reff'], $lyrics);

        // Editor menyimpan ulang semua slide sisipan: lagu tidak boleh hilang/rusak
        $custom = ScheduleCustomSlide::where('kind', 'song')->firstOrFail();
        $this->actingAs($user)->post(route('liturgy.update', $schedule), [
            'dynamic_content' => ['x' => ''],
            'custom_slides' => [$bibleItem->id => [$custom->id => ['kind' => 'song', 'title' => $custom->title, 'content' => $custom->content]]],
        ]);
        $saved = ScheduleCustomSlide::where('kind', 'song')->firstOrFail();
        $this->assertSame($custom->content, $saved->content);
        $this->assertSame($bibleItem->id, $saved->liturgy_item_id);

        $this->actingAs($user)->postJson(route('presenter.insert_song', $schedule), ['buku' => 'KJ', 'nomor' => '999'])
            ->assertStatus(422)->assertJsonPath('success', false);
    }

    public function test_quick_text_is_part_of_live_state_and_stripped_of_html(): void
    {
        $user = User::factory()->create();
        $schedule = $this->makeSchedule();

        $this->actingAs($user)->postJson(route('presenter.state.push', $schedule), ['index' => 1, 'ticker' => ' <b>Mobil DS 1234</b> mohon dipindahkan ', 'client' => 'abc'])->assertOk();
        $this->actingAs($user)->getJson(route('presenter.state', $schedule))
            ->assertJsonPath('ticker', 'Mobil DS 1234 mohon dipindahkan')->assertJsonPath('client', 'abc');

        $this->actingAs($user)->postJson(route('presenter.state.push', $schedule), ['index' => 1, 'ticker' => ''])->assertOk();
        $this->actingAs($user)->getJson(route('presenter.state', $schedule))->assertJsonPath('ticker', null);

        $this->actingAs($user)->postJson(route('presenter.state.push', $schedule), ['index' => 1, 'ticker' => str_repeat('x', 201)])->assertStatus(422);
    }
}
