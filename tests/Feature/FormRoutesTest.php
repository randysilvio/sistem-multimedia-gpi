<?php

namespace Tests\Feature;

use App\Models\Liturgy;
use App\Models\Schedule;
use App\Models\Song;
use App\Models\User;
use Database\Seeders\LaguIbadahMingguSeeder;
use Database\Seeders\TemplateIbadahMingguSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Tests\TestCase;

/**
 * Setiap <form> di halaman admin harus mengirim ke rute yang MENERIMA method-nya
 * (termasuk @method('PUT'/'DELETE')). Mencegah galat "The PUT method is not supported".
 */
class FormRoutesTest extends TestCase
{
    use RefreshDatabase;

    public function test_every_form_targets_a_route_that_accepts_its_method(): void
    {
        $this->seed([TemplateIbadahMingguSeeder::class, LaguIbadahMingguSeeder::class]);
        $user = User::factory()->create();
        $liturgy = Liturgy::first();
        $schedule = Schedule::create(['liturgy_id' => $liturgy->id, 'worship_date' => '2026-11-08']);
        $song = Song::first();

        $pages = [
            '/dashboard', '/liturgi/galeri', "/liturgi/edit/{$schedule->id}", "/liturgi/create?liturgy_id={$liturgy->id}",
            '/liturgi/builder', '/liturgi/template/create', "/liturgi/template/{$liturgy->id}/edit", '/liturgi/warta',
            '/liturgi/impor', '/songs', '/songs/create', "/songs/{$song->id}/edit", '/pengaturan', '/pengguna',
            "/pengguna/{$user->id}/edit", '/profile', "/liturgi/kontrol/{$schedule->id}",
        ];

        $checked = 0;
        foreach ($pages as $page) {
            $html = $this->actingAs($user)->get($page)->assertOk()->getContent();
            preg_match_all('/<form\b[^>]*>.*?<\/form>/is', $html, $forms);
            foreach ($forms[0] as $form) {
                if (!preg_match('/\baction="([^"]+)"/i', $form, $a)) {
                    continue;
                }
                $method = preg_match('/\bmethod="(\w+)"/i', $form, $m) ? strtoupper($m[1]) : 'GET';
                if (preg_match('/name="_method"\s+value="(\w+)"/i', $form, $spoof)) {
                    $method = strtoupper($spoof[1]);
                }
                $url = html_entity_decode($a[1]);
                try {
                    Route::getRoutes()->match(Request::create($url, $method));
                } catch (MethodNotAllowedHttpException $e) {
                    $this->fail("Form di $page mengirim $method ke $url, padahal rute tidak menerima $method.");
                } catch (NotFoundHttpException $e) {
                    $this->fail("Form di $page mengirim ke $url yang tidak punya rute.");
                }
                $checked++;
            }
        }

        $this->assertGreaterThan(15, $checked, 'cukup banyak form yang diperiksa');
    }
}
