<?php

namespace App\Http\Controllers;

use App\Services\ActivityLog;
use App\Models\Announcement;
use App\Models\Schedule;
use App\Models\ScheduleCustomSlide;
use App\Models\Song;
use App\Services\DisplaySettings;
use App\Services\PresenterState;
use App\Services\SlideBuilder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * Endpoint ringan untuk control panel & layar live:
 * data slide (JSON), simpan pengaturan tampilan, dan status tayangan
 * untuk mode 2 komputer.
 */
class PresenterController extends Controller
{
    public function __construct(private SlideBuilder $builder)
    {
    }

    /** Data yang dibutuhkan control panel / layar live. */
    public static function payload(Schedule $schedule, SlideBuilder $builder): array
    {
        $announcements = Announcement::where('is_active', true)->orderBy('order_num')->get();

        return [
            'slides' => $builder->build($schedule, $announcements),
            'settings' => DisplaySettings::for($schedule),
            'version' => PresenterState::get($schedule->id)['contentVersion'],
        ];
    }

    /** Remote dari HP (perlu login). */
    public function remote(Schedule $schedule): \Illuminate\View\View
    {
        return view('liturgy.remote', ['schedule' => $schedule->load('liturgy'), 'payload' => self::payload($schedule, $this->builder)]);
    }

    public function slides(Schedule $schedule): JsonResponse
    {
        return response()->json(self::payload($schedule, $this->builder));
    }

    public function saveSettings(Request $request, Schedule $schedule): JsonResponse
    {
        $request->validate(['settings' => 'required|array']);

        $settings = DisplaySettings::sanitize($request->input('settings'), DisplaySettings::for($schedule));
        $schedule->forceFill(['display_settings' => $settings])->save();
        $state = PresenterState::bumpContent($schedule->id);

        return response()->json(['success' => true, 'settings' => $settings, 'version' => $state['contentVersion']]);
    }

    public function state(Request $request, Schedule $schedule): JsonResponse|Response
    {
        $state = PresenterState::get($schedule->id);

        if ((int) $request->query('seq', -1) === (int) $state['seq']) {
            return response()->noContent();
        }

        return response()->json($state);
    }

    public function pushState(Request $request, Schedule $schedule): JsonResponse
    {
        $data = $request->validate([
            'index' => 'required|integer|min:0|max:100000',
            'key' => 'nullable|string|max:100',
            'blank' => 'nullable|in:black,white',
            'hideText' => 'sometimes|boolean',
            'ticker' => 'nullable|string|max:200',
            'client' => 'nullable|string|max:40',
        ]);

        $state = PresenterState::push($schedule->id, [
            'index' => (int) $data['index'],
            'key' => $data['key'] ?? null,
            'blank' => $data['blank'] ?? null,
            'hideText' => (bool) ($data['hideText'] ?? false),
            'ticker' => isset($data['ticker']) && trim($data['ticker']) !== '' ? strip_tags(trim($data['ticker'])) : null,
            'client' => $data['client'] ?? null,
        ]);

        return response()->json(['success' => true, 'seq' => $state['seq']]);
    }

    /**
     * Lagu dadakan dari Kontrol Live: sisipkan lagu dari database setelah bagian
     * yang sedang tayang/dipilih, lalu kembalikan daftar slide terbaru.
     */
    public function insertSong(Request $request, Schedule $schedule): JsonResponse
    {
        $data = $request->validate([
            'buku' => 'required|string|max:10',
            'nomor' => 'required|string|max:60',
            'bait' => 'nullable|array|max:30',
            'bait.*' => 'integer|min:1|max:99',
            'after_key' => 'nullable|string|max:100',
        ]);

        $song = Song::findByRef($data['buku'], $data['nomor']);
        if (!$song) {
            return response()->json(['success' => false, 'message' => "Lagu {$data['buku']} {$data['nomor']} tidak ada di Database Lagu."], 422);
        }

        $schedule->loadMissing('liturgy.items');
        $items = $schedule->liturgy->items;
        if ($items->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'Jadwal ini belum punya bagian liturgi.'], 422);
        }
        $itemId = preg_match('/^i(\d+)/', (string) ($data['after_key'] ?? ''), $m) && $items->contains('id', (int) $m[1])
            ? (int) $m[1]
            : $items->last()->id;

        $position = $schedule->customSlides()->where('liturgy_item_id', $itemId)->count();
        $content = $song->slideContent($data['bait'] ?? null);
        ScheduleCustomSlide::create([
            'schedule_id' => $schedule->id,
            'liturgy_item_id' => $itemId,
            'kind' => ScheduleCustomSlide::KIND_SONG,
            'title' => $content['judul'],
            'content' => json_encode($content, JSON_UNESCAPED_UNICODE),
        ]);
        ActivityLog::record('jadwal.lagu', $schedule, "Menyisipkan lagu dadakan {$content['judul']} pada jadwal " . $schedule->worship_date->translatedFormat('d F Y'));
        PresenterState::bumpContent($schedule->id);

        return response()->json([
            'success' => true,
            'judul' => $content['judul'],
            'coverKey' => "i{$itemId}-x{$position}-cover",
        ] + self::payload($schedule->fresh(), $this->builder));
    }
}
