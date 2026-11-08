<?php

namespace Tests\Feature;

use App\Models\Liturgy;
use App\Models\Schedule;
use App\Models\Setting;
use App\Models\Song;
use App\Models\User;
use App\Services\BulletinBuilder;
use App\Services\DisplaySettings;
use Database\Seeders\LaguIbadahMingguSeeder;
use Database\Seeders\TemplateIbadahMingguSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class UxFeaturesTest extends TestCase
{
    use RefreshDatabase;

    private function scheduleMingguIV(): Schedule
    {
        $this->seed([TemplateIbadahMingguSeeder::class, LaguIbadahMingguSeeder::class]);
        $liturgy = Liturgy::where('name', TemplateIbadahMingguSeeder::NAMES['IV'])->first();
        $schedule = Schedule::create(['liturgy_id' => $liturgy->id, 'worship_date' => '2026-11-22', 'theme' => 'Uji Tema', 'preacher_name' => 'Pdt. A']);
        $slot = $liturgy->items->first(fn ($i) => $i->title === 'Nyanyian Persiapan');
        $schedule->details()->create(['liturgy_item_id' => $slot->id, 'dynamic_content' => Song::where('book', 'DSL')->where('number', '136')->first()->slideContent([1, 2])]);
        $bible = $liturgy->items->first(fn ($i) => $i->title === 'Bacaan Alkitab');
        $schedule->details()->create(['liturgy_item_id' => $bible->id, 'dynamic_content' => ['ref' => 'Efesus 1:15-23', 'content' => '15 ayat']]);

        return $schedule;
    }

    public function test_bulletin_blocks_follow_the_order_of_service(): void
    {
        $schedule = $this->scheduleMingguIV();
        $blocks = collect(app(BulletinBuilder::class)->blocks($schedule));

        $this->assertSame('cover', $blocks[0]['t']);
        $this->assertSame('TATA IBADAH MINGGU IV', $blocks[0]['title']);
        $song = $blocks->firstWhere('t', 'song');
        $this->assertSame(['Nyanyian Persiapan', 'DSL 136', '1, 2', 'Jam Sembahyang'], [$song['label'], $song['code'], $song['verses'], $song['title']]);
        $verses = $blocks->where('t', 'verse')->values();
        $this->assertNotNull($verses[0]['reff'], 'reff ditulis sekali setelah bait pertama');
        $this->assertTrue($verses[1]['reffMark'], 'bait berikutnya cukup ditandai …Reff');
        $this->assertTrue($blocks->contains(fn ($b) => $b['t'] === 'heading' && $b['text'] === 'PEMBACAAN ALKITAB : EFESUS 1:15-23'));
        $this->assertTrue($blocks->contains(fn ($b) => $b['t'] === 'rubric' && $b['text'] === '(Jemaat Berdiri)'));
        $votum = $blocks->first(fn ($b) => $b['t'] === 'line' && str_starts_with($b['text'], 'Asal dan sumber hidup'));
        $this->assertSame('PF', $votum['speaker']);

        $this->actingAs(User::factory()->create())->get(route('liturgy.bulletin', $schedule))
            ->assertOk()->assertSee('Booklet (A4 dilipat)')->assertSee('Jam sembahyang yang kudus', false);
    }

    public function test_phone_remote_page_and_state_from_phone(): void
    {
        $schedule = $this->scheduleMingguIV();
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('presenter.remote', $schedule))->assertOk()->assertSee('Berikutnya')->assertSee('PRESENTER', false);
        $this->get(route('presenter.remote', $schedule)); // tetap login

        $this->actingAs($user)->postJson(route('presenter.state.push', $schedule), ['index' => 5, 'key' => 'x', 'client' => 'remote-abc'])->assertOk();
        $this->actingAs($user)->getJson(route('presenter.state', $schedule))->assertJsonPath('client', 'remote-abc')->assertJsonPath('index', 5);

        auth()->logout();
        $this->get(route('presenter.remote', $schedule))->assertRedirect(route('login'));
    }

    public function test_video_background_upload_and_setting(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $this->assertSame('video', DisplaySettings::sanitize(['design' => ['bg' => 'video']])['design']['bg']);
        $this->assertNull(\App\Services\Branding::videoUrl(), 'tanpa unggahan tidak ada video latar (video-gereja.mp4 bukan latar)');
        $schedule = $this->scheduleMingguIV();
        $this->actingAs($user)->get(route('liturgy.control', $schedule))->assertDontSee('data-bg="video"', false);

        $bad = UploadedFile::fake()->create('latar.mp4', 100, 'image/png');
        $this->actingAs($user)->post(route('settings.update'), ['background_video' => $bad])->assertSessionHasErrors('background_video');

        $video = UploadedFile::fake()->create('latar.mp4', 500, 'video/mp4');
        $this->actingAs($user)->post(route('settings.update'), ['background_video' => $video])->assertSessionHasNoErrors();
        $path = Setting::get('background_video');
        $this->assertNotNull($path);
        Storage::disk('public')->assertExists($path);
        $this->assertStringContainsString('storage/' . $path, \App\Services\Branding::videoUrl());

        $this->actingAs($user)->post(route('settings.update'), ['remove_video' => 1]);
        Storage::disk('public')->assertMissing($path);
        $this->assertNull(Setting::get('background_video'));
    }

    public function test_control_panel_has_new_tools(): void
    {
        $schedule = $this->scheduleMingguIV();
        $this->actingAs(User::factory()->create())->get(route('liturgy.control', $schedule))->assertOk()
            ->assertSee('Lagu Dadakan')->assertSee('Teks Cepat')->assertSee('Cari Slide')->assertSee('Uji Tayang')
            ->assertSee('Remote HP')->assertSee('Warna masa raya')->assertSee('Cetak buletin')->assertSee('sw.js', false)
            ->assertSeeInOrder(['Beranda', 'Sisipkan', 'Tayangan Slide', 'Latar', 'Teks', 'Lagu', 'Layar', 'Isi &amp; Cetak'], false)
            ->assertSee('data-song-style="nomor"', false)->assertSee('data-song-style="terang"', false);
    }

    public function test_song_style_setting_is_sanitized(): void
    {
        $this->assertSame('nomor', DisplaySettings::sanitize([])['design']['songStyle']);
        $this->assertSame('terang', DisplaySettings::sanitize(['design' => ['songStyle' => 'terang']])['design']['songStyle']);
        $this->assertSame('nomor', DisplaySettings::sanitize(['design' => ['songStyle' => '<script>']])['design']['songStyle']);
    }
}
