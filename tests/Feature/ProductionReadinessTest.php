<?php

namespace Tests\Feature;

use App\Models\Liturgy;
use App\Models\Song;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Hal-hal yang wajib benar sebelum aplikasi dibuka di internet.
 */
class ProductionReadinessTest extends TestCase
{
    use RefreshDatabase;

    public function test_security_headers_are_sent(): void
    {
        $r = $this->get('/login');
        $r->assertHeader('X-Content-Type-Options', 'nosniff');
        $r->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        $r->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        $this->assertStringContainsString('camera=(self)', $r->headers->get('Permissions-Policy'));
    }

    public function test_logged_in_pages_are_not_cached(): void
    {
        $this->actingAs(User::factory()->create())->get('/dashboard')
            ->assertOk()->assertHeader('Cache-Control', 'no-store, private');
    }

    public function test_login_is_rate_limited(): void
    {
        User::factory()->create(['email' => 'a@b.c']);
        for ($i = 0; $i < 20; $i++) {
            $this->post('/login', ['email' => 'a@b.c', 'password' => 'salah']);
        }
        $this->post('/login', ['email' => 'a@b.c', 'password' => 'salah'])->assertStatus(429);
    }

    public function test_validation_messages_are_in_indonesian(): void
    {
        $this->from('/login')->post('/login', ['email' => '', 'password' => ''])
            ->assertSessionHasErrors(['email' => 'email wajib diisi.']);
    }

    public function test_friendly_error_pages(): void
    {
        $this->get('/halaman-yang-tidak-ada')->assertNotFound()->assertSee('Halaman tidak ditemukan')->assertDontSee('Laravel');
    }

    public function test_users_can_be_managed_but_not_self_deleted(): void
    {
        $admin = User::factory()->create();
        $this->actingAs($admin)->get(route('users.index'))->assertOk()->assertSee($admin->email);

        $this->actingAs($admin)->post(route('users.store'), [
            'name' => 'Operator Baru', 'email' => 'Operator@Gereja.org', 'password' => 'rahasia123', 'password_confirmation' => 'rahasia123',
        ])->assertRedirect(route('users.index'));
        $op = User::where('email', 'operator@gereja.org')->firstOrFail();
        $this->assertNotNull($op->email_verified_at);

        // Operator bisa langsung masuk
        auth()->logout();
        $this->post('/login', ['email' => 'operator@gereja.org', 'password' => 'rahasia123'])->assertRedirect('/dashboard');

        // Atur ulang kata sandi oleh admin
        $this->actingAs($admin)->put(route('users.update', $op), [
            'name' => 'Operator', 'email' => 'operator@gereja.org', 'password' => 'sandibaru99', 'password_confirmation' => 'sandibaru99',
        ])->assertRedirect(route('users.index'));
        $this->assertTrue(\Hash::check('sandibaru99', $op->fresh()->password));

        $this->actingAs($admin)->delete(route('users.destroy', $admin))->assertSessionHas('error');
        $this->assertNotNull($admin->fresh());
        $this->actingAs($admin)->delete(route('users.destroy', $op))->assertRedirect(route('users.index'));
        $this->assertNull($op->fresh());

        // Tamu tidak bisa membuat akun
        auth()->logout();
        $this->post(route('users.store'), ['name' => 'x', 'email' => 'x@x.x', 'password' => 'xxxxxxxx', 'password_confirmation' => 'xxxxxxxx'])
            ->assertRedirect('/login');
    }

    public function test_announcement_upload_uses_real_file_type_not_client_extension(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $real = UploadedFile::fake()->image('warta.png', 200, 120); // tetap dirujuk agar file sementara tidak terhapus

        // Berekstensi .php: ditolak sama sekali
        $php = new UploadedFile($real->getPathname(), 'warta.php', 'image/png', null, true);
        $this->actingAs($user)->post(route('announcement.store'), ['image' => $php])->assertSessionHasErrors('image');
        $this->assertCount(0, Storage::disk('public')->allFiles('sinode'));

        // PNG asli yang dinamai .jpg: disimpan sesuai ISI file (.png), bukan nama kiriman
        $renamed = new UploadedFile($real->getPathname(), 'warta.jpg', 'image/jpeg', null, true);
        $this->actingAs($user)->post(route('announcement.store'), ['image' => $renamed, 'title' => 'Uji'])->assertSessionHasNoErrors();
        $files = Storage::disk('public')->allFiles('sinode');
        $this->assertCount(1, $files);
        $this->assertStringEndsWith('.png', $files[0]);

        // Bukan gambar ditolak
        // (file sungguhan, bukan File::fake yang menebak tipe dari nama: isi diperiksa finfo)
        $path = tempnam(sys_get_temp_dir(), 'up');
        file_put_contents($path, "<?php echo 'jahat';");
        $text = new UploadedFile($path, 'x.png', 'image/png', null, true);
        $this->actingAs($user)->post(route('announcement.store'), ['image' => $text])->assertSessionHasErrors('image');
        $this->assertCount(1, Storage::disk('public')->allFiles('sinode'));
        @unlink($path);
    }

    public function test_seeder_is_safe_to_rerun_and_creates_admin_once(): void
    {
        Liturgy::create(['name' => 'Template buatan jemaat']);
        $this->seed();
        $this->seed();

        $this->assertSame(1, User::count());
        $this->assertTrue(Liturgy::where('name', 'Template buatan jemaat')->exists(), 'seeder tidak boleh mengosongkan template');
        $this->assertGreaterThan(30, Song::count());
    }

    public function test_admin_command_creates_and_resets(): void
    {
        $this->artisan('admin:buat', ['--email' => 'Root@Gereja.org', '--name' => 'Root', '--password' => 'rahasia123'])->assertSuccessful();
        $this->assertTrue(User::where('email', 'root@gereja.org')->exists());

        $this->artisan('admin:buat', ['--email' => 'root@gereja.org', '--password' => 'gantilagi1'])->assertSuccessful();
        $this->assertSame(1, User::count());
        $this->assertTrue(\Hash::check('gantilagi1', User::first()->password));

        $this->artisan('admin:buat', ['--email' => 'bukan-email', '--name' => 'X', '--password' => 'pendek'])->assertFailed();
    }

    public function test_song_search_by_book_and_number_and_numeric_order(): void
    {
        foreach (['2', '14', '21'] as $n) {
            Song::create(['book' => 'KJ', 'number' => $n, 'title' => "Lagu $n", 'verses' => ['a']]);
        }
        Song::create(['book' => 'NKB', 'number' => '21', 'title' => 'Lain', 'verses' => ['kasih setia']]);
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('songs.index', ['buku' => 'KJ']))->assertOk()->assertSeeInOrder(['Lagu 2', 'Lagu 14', 'Lagu 21']);
        $this->actingAs($user)->get(route('songs.index', ['search' => 'kj 21']))->assertSee('Lagu 21')->assertDontSee('Lain');
        $this->actingAs($user)->get(route('songs.index', ['search' => 'setia']))->assertSee('Lain')->assertDontSee('Lagu 2');
    }

    public function test_all_admin_pages_render_with_local_assets_only(): void
    {
        $user = User::factory()->create();
        foreach (['/dashboard', '/liturgi/galeri', '/songs', '/songs/create', '/pengaturan', '/pengguna', '/profile', '/liturgi/warta', '/liturgi/template/create', '/liturgi/builder'] as $url) {
            $html = $this->actingAs($user)->get($url)->assertOk()->getContent();
            $this->assertStringNotContainsString('cdn.jsdelivr.net', $html, $url);
            $this->assertStringNotContainsString('fonts.googleapis.com', $html, $url);
            $this->assertStringContainsString('noindex', $html, $url);
        }
    }
}
