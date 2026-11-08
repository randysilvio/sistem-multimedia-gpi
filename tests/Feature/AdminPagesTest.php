<?php

namespace Tests\Feature;

use App\Models\Liturgy;
use App\Models\Schedule;
use App\Models\Setting;
use App\Models\Song;
use App\Models\User;
use App\Services\Branding;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminPagesTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create();
    }

    public function test_settings_page_requires_login_and_renders(): void
    {
        $this->get(route('settings.edit'))->assertRedirect(route('login'));
        $this->actingAs($this->admin())->get(route('settings.edit'))->assertOk()->assertSee('Logo');
    }

    public function test_logo_upload_replace_and_remove(): void
    {
        Storage::fake('public');
        $user = $this->admin();

        $this->actingAs($user)->post(route('settings.update'), [
            'church_name' => 'GPI Papua Jemaat Imanuel',
            'logo' => UploadedFile::fake()->image('logo gereja.png', 400, 400),
        ])->assertRedirect(route('settings.edit'))->assertSessionHas('success');

        $first = Setting::get('logo_path');
        $this->assertNotNull($first);
        Storage::disk('public')->assertExists($first);
        $this->assertStringContainsString('storage/' . $first, Branding::logoUrl());
        $this->assertSame('GPI Papua Jemaat Imanuel', Branding::churchName());

        // Ganti logo: file lama dihapus
        $this->actingAs($user)->post(route('settings.update'), [
            'church_name' => 'GPI Papua Jemaat Imanuel',
            'logo' => UploadedFile::fake()->image('baru.jpg', 300, 300),
        ])->assertRedirect();
        $second = Setting::get('logo_path');
        $this->assertNotSame($first, $second);
        Storage::disk('public')->assertMissing($first);
        Storage::disk('public')->assertExists($second);

        // Hapus logo: kembali ke logo bawaan
        $this->actingAs($user)->post(route('settings.update'), ['church_name' => 'X', 'remove_logo' => '1'])->assertRedirect();
        $this->assertNull(Setting::get('logo_path'));
        Storage::disk('public')->assertMissing($second);
        $this->assertStringNotContainsString('storage/branding', Branding::logoUrl());
    }

    public function test_logo_upload_rejects_svg_and_non_images(): void
    {
        Storage::fake('public');
        $user = $this->admin();

        $svg = UploadedFile::fake()->createWithContent('logo.svg', '<svg xmlns="http://www.w3.org/2000/svg"><script>alert(1)</script></svg>');
        $this->actingAs($user)->post(route('settings.update'), ['church_name' => 'A', 'logo' => $svg])->assertSessionHasErrors('logo');

        $pdf = UploadedFile::fake()->create('logo.pdf', 10, 'application/pdf');
        $this->actingAs($user)->post(route('settings.update'), ['church_name' => 'A', 'logo' => $pdf])->assertSessionHasErrors('logo');

        $this->assertNull(Setting::get('logo_path'));
    }

    public function test_uploaded_logo_is_used_by_live_screen(): void
    {
        Storage::fake('public');
        $user = $this->admin();
        $this->actingAs($user)->post(route('settings.update'), ['church_name' => 'A', 'logo' => UploadedFile::fake()->image('l.png')]);

        $liturgy = Liturgy::create(['name' => 'Ibadah']);
        $schedule = Schedule::create(['liturgy_id' => $liturgy->id, 'worship_date' => '2026-11-08']);
        $path = Setting::get('logo_path');

        $this->actingAs($user)->get(route('liturgy.presentation', $schedule))->assertOk()->assertSee($path, false);
    }

    public function test_dashboard_shows_stats_and_next_service(): void
    {
        $liturgy = Liturgy::create(['name' => 'Ibadah Minggu']);
        Schedule::create(['liturgy_id' => $liturgy->id, 'worship_date' => now()->subDays(7)->toDateString(), 'theme' => 'Tema Lalu']);
        Schedule::create(['liturgy_id' => $liturgy->id, 'worship_date' => now()->addDays(3)->toDateString(), 'theme' => 'Tema Berikutnya']);
        Song::create(['book' => 'KJ', 'number' => '1', 'title' => 'Haleluya', 'verses' => ['a']]);

        $this->actingAs($this->admin())->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Tema Berikutnya')
            ->assertSee('Ibadah berikutnya');
    }

    public function test_gallery_lists_only_real_templates(): void
    {
        Liturgy::create(['name' => 'Template Minggu']);
        Liturgy::create(['name' => 'Natal Sekolah Minggu (Custom)']);

        $this->actingAs($this->admin())->get(route('liturgy.gallery'))
            ->assertOk()
            ->assertSee('Template Minggu')
            ->assertDontSee('Natal Sekolah Minggu (Custom)');
    }

    public function test_all_admin_pages_render_inside_shared_layout(): void
    {
        $user = $this->admin();
        $liturgy = Liturgy::create(['name' => 'Ibadah Minggu']);
        $liturgy->items()->create(['title' => 'Nyanyian', 'is_dynamic' => true, 'order_number' => 1]);
        $schedule = Schedule::create(['liturgy_id' => $liturgy->id, 'worship_date' => '2026-11-08']);
        $song = Song::create(['book' => 'KJ', 'number' => '1', 'title' => 'Haleluya', 'verses' => ['a']]);

        $urls = [
            route('dashboard'),
            route('liturgy.gallery'),
            route('liturgy.builder'),
            route('liturgy.create', ['liturgy_id' => $liturgy->id]),
            route('liturgy.edit', $schedule),
            route('liturgy.template.create'),
            route('liturgy.template.edit', $liturgy->id),
            route('announcement.index'),
            route('songs.index'),
            route('songs.create'),
            route('songs.edit', $song),
            route('settings.edit'),
        ];

        foreach ($urls as $url) {
            $html = $this->actingAs($user)->get($url)->assertOk()->getContent();
            $this->assertStringContainsString('data-admin-layout', $html, "$url belum memakai layout admin");
            $this->assertSame(1, substr_count($html, '<html'), "$url memiliki <html> ganda");
            $this->assertStringNotContainsString('gpipapua.org/storage/logos', str_replace(Branding::logoUrl(), '', $html), "$url masih memakai logo hardcode");
        }
    }
}
