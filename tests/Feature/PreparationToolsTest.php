<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\Liturgy;
use App\Models\Schedule;
use App\Models\ScheduleCustomSlide;
use App\Models\Song;
use App\Models\User;
use App\Services\BulletinParser;
use App\Services\ReadinessCheck;
use Database\Seeders\LaguIbadahMingguSeeder;
use Database\Seeders\TemplateIbadahMingguSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PreparationToolsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([TemplateIbadahMingguSeeder::class, LaguIbadahMingguSeeder::class]);
        Http::fake(['*' => Http::response('', 500)]); // tanpa internet: ayat tidak bisa ditarik
    }

    private function fixture(string $name): string
    {
        return file_get_contents(base_path("tests/Fixtures/$name"));
    }

    private function picks(array $result): array
    {
        return collect($result['slots'])->mapWithKeys(fn ($s) => [$s['title'] . '#' . $s['item_id'] => $s['pick'] ? $s['pick']['book'] . ' ' . $s['pick']['number'] : null])->values()->all();
    }

    public function test_bulletin_minggu_iv_is_read_into_template_date_bible_and_song_slots(): void
    {
        $r = app(BulletinParser::class)->parse($this->fixture('buletin-minggu-iv.txt'));

        $this->assertSame(TemplateIbadahMingguSeeder::NAMES['IV'], $r['liturgy']->name);
        $this->assertSame('2026-11-22', $r['date']);
        $this->assertSame('Efesus 1:15-23', $r['bible']);
        $this->assertSame(['DSL 136', 'DSL 7', 'DSL 139', 'KJ 56', 'DSL 112', 'DSL 181', 'DSL 109', 'DSL 188'], $this->picks($r));
        $this->assertSame([1, 3], collect($r['slots'])->last()['pick']['verses']);
        $this->assertNull(collect($r['slots'])->firstWhere('pick.number', '181')['pick']['verses'], '"1 dst" = semua bait');
    }

    public function test_bulletin_adven_with_untitled_songs_and_template_songs(): void
    {
        $r = app(BulletinParser::class)->parse($this->fixture('buletin-adven-booklet.txt'));

        $this->assertSame(TemplateIbadahMingguSeeder::NAMES['ADVEN'], $r['liturgy']->name);
        $this->assertSame('2026-11-29', $r['date']);
        $this->assertSame('Menanti Datangnya Damai Allah', $r['theme']);
        $this->assertSame('Mikha 4:1-5', $r['bible']);
        $this->assertSame(['GPM 1', 'KJ 81', 'PKJ 42', 'KJ 85', 'GPM 65', 'KJ 280', 'BEBAS 3', 'NKB 201', 'BEBAS 2', 'BEBAS 1'], $this->picks($r));
        $this->assertSame(['PKJ 292'], $r['templateSongs'], 'Amin PKJ 292 sudah tertulis di template');
        $this->assertSame([], $r['unused'], 'iringan instrumen PKJ 46 bukan lagu yang dinyanyikan');
    }

    public function test_import_flow_review_then_create_schedule_with_warnings(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->post(route('liturgy.import.review'), ['text' => $this->fixture('buletin-minggu-iv.txt')])
            ->assertOk()->assertSee('Ibadah Minggu IV')->assertSee('Efesus 1:15-23')->assertSee('lirik belum lengkap');

        $liturgy = Liturgy::where('name', TemplateIbadahMingguSeeder::NAMES['IV'])->first();
        $slots = $liturgy->items->filter(fn ($i) => str_starts_with($i->title, 'Nyanyian'))->values();
        $songs = [];
        foreach ([['DSL', '136', '1, 2'], ['DSL', '7', ''], ['DSL', '139', '1,2'], ['KJ', '56', ''], ['DSL', '112', ''], ['DSL', '181', ''], ['DSL', '109', ''], ['DSL', '188', '1 3']] as $i => [$b, $n, $v]) {
            $songs[$slots[$i]->id] = ['book' => $b, 'number' => $n, 'verses' => $v];
        }

        $this->actingAs($user)->post(route('liturgy.import.store'), [
            'liturgy_id' => $liturgy->id, 'worship_date' => '2026-11-22', 'theme' => 'Uji', 'preacher_name' => '',
            'bible' => 'Efesus 1:15-23', 'songs' => $songs,
        ])->assertRedirect()->assertSessionHas('warnings');

        $schedule = Schedule::latest('id')->first();
        $this->assertSame('2026-11-22', $schedule->worship_date->toDateString());
        $details = $schedule->details->keyBy('liturgy_item_id');
        $this->assertSame(['1', 'ref_1', '3', 'ref_3'], array_map('strval', array_keys($details[$slots[7]->id]->dynamic_content['bait'])));
        $bible = $details->first(fn ($d) => isset($d->dynamic_content['ref']));
        $this->assertSame('Efesus 1:15-23', $bible->dynamic_content['ref'], 'referensi tetap disimpan walau ayat gagal ditarik');
        $this->assertTrue(ActivityLog::where('action', 'jadwal.impor')->exists());
    }

    public function test_duplicate_copies_content_and_inserted_songs(): void
    {
        $user = User::factory()->create();
        $liturgy = Liturgy::first();
        $item = $liturgy->items->first();
        $src = Schedule::create(['liturgy_id' => $liturgy->id, 'worship_date' => '2026-11-08', 'theme' => 'Lama', 'display_settings' => ['hidden' => ['x']]]);
        $src->details()->create(['liturgy_item_id' => $item->id, 'dynamic_content' => ['custom_title' => '', 'content' => 'HALO']]);
        $src->customSlides()->create(['liturgy_item_id' => $item->id, 'kind' => 'song', 'title' => 'KJ 1', 'content' => '{"judul":"KJ 1","bait":{"1":"a"}}']);

        $this->actingAs($user)->post(route('liturgy.duplicate', $src), ['worship_date' => '2026-11-15'])->assertRedirect();

        $copy = Schedule::latest('id')->first();
        $this->assertNotSame($src->id, $copy->id);
        $this->assertSame('2026-11-15', $copy->worship_date->toDateString());
        $this->assertSame('Lama', $copy->theme);
        $this->assertSame('HALO', $copy->details->first()->dynamic_content['content']);
        $this->assertSame('song', ScheduleCustomSlide::where('schedule_id', $copy->id)->first()->kind);
        $this->assertSame(2, Schedule::count());
    }

    public function test_readiness_lists_what_is_missing(): void
    {
        $liturgy = Liturgy::where('name', TemplateIbadahMingguSeeder::NAMES['IV'])->first();
        $schedule = Schedule::create(['liturgy_id' => $liturgy->id, 'worship_date' => now()->addDays(2)->toDateString()]);
        $slot = $liturgy->items->first(fn ($i) => str_starts_with($i->title, 'Nyanyian'));
        $schedule->details()->create(['liturgy_item_id' => $slot->id, 'dynamic_content' => Song::where('book', 'DSL')->where('number', '188')->first()->slideContent()]);

        $text = collect(ReadinessCheck::for($schedule))->pluck('text')->implode(' | ');
        $this->assertStringContainsString('Slot lagu belum diisi', $text);
        $this->assertStringContainsString('Bacaan Alkitab belum diisi', $text);
        $this->assertStringContainsString('DSL 188', $text, 'lirik belum lengkap diingatkan');
        $this->assertStringContainsString('Pelayan Firman', $text);

        $this->actingAs(User::factory()->create())->get(route('dashboard'))->assertOk()->assertSee('Cek kesiapan ibadah')->assertSee('Uji Tayang');
    }

    public function test_activity_history_records_and_lists_changes(): void
    {
        $user = User::factory()->create(['name' => 'Operator Satu']);
        $this->actingAs($user)->post(route('songs.store'), ['book' => 'kj', 'number' => '999', 'title' => 'Lagu Uji', 'verses' => ['a']])->assertRedirect();

        $log = ActivityLog::latest('id')->first();
        $this->assertSame('lagu.tambah', $log->action);
        $this->assertSame($user->id, $log->user_id);
        $this->actingAs($user)->get(route('activity.index'))->assertOk()->assertSee('Operator Satu')->assertSee('KJ 999 - Lagu Uji');
    }

    public function test_import_pages_render(): void
    {
        $this->actingAs(User::factory()->create())->get(route('liturgy.import'))->assertOk()->assertSee('Pilih PDF buletin');
        Schedule::create(['liturgy_id' => Liturgy::first()->id, 'worship_date' => '2026-11-08']);
        $this->actingAs(User::factory()->create())->get(route('liturgy.gallery'))->assertOk()->assertSee('Buat dari Buletin')->assertSee('Duplikat');
    }
}
