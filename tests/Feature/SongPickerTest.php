<?php

namespace Tests\Feature;

use App\Models\Liturgy;
use App\Models\Song;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SongPickerTest extends TestCase
{
    use RefreshDatabase;

    public function test_song_list_is_ordered_by_book_then_number(): void
    {
        Song::create(['book' => 'NKB', 'number' => '2', 'title' => 'Kami Puji', 'verses' => ['a']]);
        Song::create(['book' => 'KJ', 'number' => '21', 'title' => 'Bagi Yesus', 'verses' => ['a']]);
        Song::create(['book' => 'KJ', 'number' => '3', 'title' => 'Kepada Bapa', 'verses' => ['a'], 'is_incomplete' => true]);
        Song::create(['book' => 'KJ', 'number' => '14', 'title' => 'Hai Muliakan', 'verses' => ['a']]);

        $res = $this->actingAs(User::factory()->create())->getJson(route('api.daftar_lagu'))->assertOk();

        $this->assertSame(['KJ 3', 'KJ 14', 'KJ 21', 'NKB 2'], collect($res->json('songs'))->map(fn ($s) => "{$s['book']} {$s['number']}")->all());
        $this->assertTrue($res->json('songs.0.incomplete'));
        $this->assertSame('Kepada Bapa', $res->json('songs.0.title'));
    }

    public function test_song_list_requires_login(): void
    {
        $this->getJson(route('api.daftar_lagu'))->assertUnauthorized();
    }

    public function test_pages_with_song_slots_offer_the_picker(): void
    {
        $user = User::factory()->create();
        $liturgy = Liturgy::create(['name' => 'Ibadah Uji', 'description' => '']);
        $liturgy->items()->create(['title' => 'Nyanyian Pembukaan', 'is_dynamic' => true, 'order_number' => 1]);

        $this->actingAs($user)->get(route('liturgy.create', $liturgy))->assertOk()
            ->assertSee('data-song-picker', false)->assertSee('data-song-pull', false)
            ->assertSee('name="song-list-url"', false);
        $this->actingAs($user)->get(route('liturgy.builder'))->assertOk()
            ->assertSee('data-song-picker', false);
    }
}
