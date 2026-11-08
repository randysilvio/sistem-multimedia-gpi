<?php

namespace App\Http\Controllers;

use App\Models\Liturgy;
use App\Models\Schedule;
use App\Models\Song;
use App\Services\ActivityLog;
use App\Services\BulletinParser;
use App\Services\ScheduleFactory;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Alat persiapan jadwal: buat jadwal dari teks/PDF buletin, dan duplikat jadwal.
 */
class ScheduleToolsController extends Controller
{
    public function importForm(): View
    {
        return view('liturgy.import', ['liturgies' => Liturgy::templates()->orderBy('id')->get()]);
    }

    /** Baca teks buletin lalu tampilkan hasil tebakan untuk diperiksa. */
    public function importReview(Request $request, BulletinParser $parser): View
    {
        $data = $request->validate([
            'text' => 'required|string|max:60000',
            'liturgy_id' => 'nullable|integer|exists:liturgies,id',
        ], [], ['text' => 'teks tata ibadah']);

        $forced = !empty($data['liturgy_id']) ? Liturgy::find($data['liturgy_id']) : null;
        $result = $parser->parse($data['text'], $forced);

        // Judul lagu dari database untuk pratinjau
        foreach ($result['slots'] as &$slot) {
            $song = $slot['pick'] ? Song::findByRef($slot['pick']['book'], $slot['pick']['number']) : null;
            $slot['song'] = $song;
        }
        unset($slot);

        return view('liturgy.import_review', [
            'text' => $data['text'],
            'result' => $result,
            'liturgies' => Liturgy::templates()->orderBy('id')->get(),
            'date' => $result['date'] ?? Carbon::now()->next(Carbon::SUNDAY)->toDateString(),
        ]);
    }

    public function importStore(Request $request, ScheduleFactory $factory): RedirectResponse
    {
        $data = $request->validate([
            'liturgy_id' => 'required|integer|exists:liturgies,id',
            'worship_date' => 'required|date',
            'theme' => 'nullable|string|max:255',
            'preacher_name' => 'nullable|string|max:255',
            'bible' => 'nullable|string|max:200',
            'songs' => 'nullable|array',
            'songs.*.book' => 'nullable|string|max:10',
            'songs.*.number' => 'nullable|string|max:60',
            'songs.*.verses' => 'nullable|string|max:60',
        ], [], ['worship_date' => 'tanggal ibadah', 'liturgy_id' => 'template']);

        $songs = [];
        foreach ((array) ($data['songs'] ?? []) as $itemId => $pick) {
            $verses = array_values(array_filter(array_map('intval', preg_split('/[^\d]+/', (string) ($pick['verses'] ?? ''))), fn ($n) => $n > 0 && $n < 100));
            $songs[(int) $itemId] = ['book' => $pick['book'] ?? '', 'number' => trim((string) ($pick['number'] ?? '')), 'verses' => $verses ?: null];
        }

        $liturgy = Liturgy::findOrFail($data['liturgy_id']);
        ['schedule' => $schedule, 'warnings' => $warnings] = $factory->create(
            $liturgy, $data['worship_date'], $data['theme'] ?? null, $data['preacher_name'] ?? null, $songs, $data['bible'] ?? null,
        );
        ActivityLog::record('jadwal.impor', $schedule, "Membuat jadwal {$liturgy->name} " . $schedule->worship_date->translatedFormat('d F Y') . ' dari buletin');

        return redirect()->route('liturgy.edit', $schedule)
            ->with('success', 'Jadwal dibuat dari buletin. Periksa isinya, lalu buka Kontrol Live.')
            ->with('warnings', $warnings);
    }

    /** Cetak buletin tata ibadah (A5 berurutan atau booklet A4 dilipat). */
    public function bulletin(Schedule $schedule, \App\Services\BulletinBuilder $builder): View
    {
        return view('liturgy.bulletin', ['schedule' => $schedule->load('liturgy'), 'blocks' => $builder->blocks($schedule)]);
    }

    public function duplicate(Request $request, Schedule $schedule, ScheduleFactory $factory): RedirectResponse
    {
        $data = $request->validate(['worship_date' => 'required|date'], [], ['worship_date' => 'tanggal ibadah']);
        $copy = $factory->duplicate($schedule, $data['worship_date']);
        ActivityLog::record('jadwal.duplikat', $copy, 'Menduplikat jadwal ' . $schedule->worship_date->translatedFormat('d F Y') . ' ke ' . $copy->worship_date->translatedFormat('d F Y'));

        return redirect()->route('liturgy.edit', $copy)
            ->with('success', 'Jadwal diduplikat. Ganti lagu, ayat, tema dan pelayan firman untuk tanggal baru ini.');
    }
}
