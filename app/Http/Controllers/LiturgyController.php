<?php

namespace App\Http\Controllers;

use App\Services\ActivityLog;
use Illuminate\Http\Request;
use App\Models\Liturgy;
use App\Models\Schedule;
use App\Models\ScheduleDetail;
use App\Models\ScheduleCustomSlide;
use App\Models\Announcement; 
use Carbon\Carbon;
use App\Services\Bible;
use App\Services\DisplaySettings;
use App\Services\PresenterState;
use App\Services\SlideBuilder;

class LiturgyController extends Controller
{
    public function gallery()
    {
        Carbon::setLocale('id');
        $liturgies = Liturgy::templates()->orderBy('id')->get();
        $schedules = Schedule::with('liturgy')->orderBy('worship_date', 'desc')->orderByDesc('id')->paginate(15);
        
        return view('liturgy.gallery', compact('schedules', 'liturgies'));
    }

    public function create(Request $request)
    {
        $liturgies = Liturgy::templates()->orderBy('id')->get();
        if ($liturgies->isEmpty()) {
            return redirect()->route('liturgy.template.create')->with('error', 'Belum ada template tata ibadah. Buat template dulu.');
        }

        $selectedLiturgyId = $request->input('liturgy_id', $liturgies->first()->id);
        $liturgy = Liturgy::with(['items' => function($query) { 
            $query->orderBy('order_number'); 
        }])->findOrFail($selectedLiturgyId);

        return view('liturgy.create', compact('liturgies', 'liturgy'));
    }

    public function builder()
    {
        return view('liturgy.builder');
    }

    public function store(Request $request)
    {
        $request->validate([
            'worship_date' => 'required|date',
            'dynamic_content' => 'required|array'
        ]);

        $schedule = Schedule::create([
            'liturgy_id' => $request->liturgy_id,
            'worship_date' => $request->worship_date,
            'theme' => $request->theme,
            'preacher_name' => $request->preacher_name,
            'theme_color' => $request->theme_color ?? '#1b2735',
            'display_settings' => DisplaySettings::inheritedForNewSchedule(),
        ]);

        $this->saveDetails($schedule, $request);

        ActivityLog::record('jadwal.tambah', $schedule, 'Membuat jadwal ' . ($schedule->liturgy->name ?? '') . ' ' . $schedule->worship_date->translatedFormat('d F Y'));
        return redirect()->route('liturgy.edit', $schedule->id)
                         ->with('success', 'Jadwal berhasil dibuat. Selamat melayani!');
    }

    public function storeCustom(Request $request)
    {
        $request->validate([
            'schedule_name' => 'required|string',
            'worship_date' => 'required|date',
            'blocks' => 'required|array'
        ]);

        $liturgy = Liturgy::create([
            'name' => $request->schedule_name . ' (Custom)'
        ]);

        $schedule = Schedule::create([
            'liturgy_id' => $liturgy->id,
            'worship_date' => $request->worship_date,
            'theme' => $request->schedule_name,
            'preacher_name' => $request->preacher_name,
            'theme_color' => $request->theme_color ?? '#1b2735',
            'display_settings' => DisplaySettings::inheritedForNewSchedule(),
        ]);

        $this->processBlocks($request->blocks, $liturgy, $schedule);

        return redirect()->route('liturgy.edit', $schedule->id)
                         ->with('success', 'Presentasi Kustom berhasil dirakit!');
    }

    public function edit(Schedule $schedule)
    {
        $schedule->load(['details', 'customSlides', 'liturgy.items']);
        return view('liturgy.edit', compact('schedule'));
    }

    public function controlPanel(Schedule $schedule, SlideBuilder $builder)
    {
        $schedule->load(['details', 'customSlides', 'liturgy.items']);
        $payload = PresenterController::payload($schedule, $builder);

        return view('liturgy.control_panel', [
            'schedule' => $schedule,
            'payload' => $payload,
            'backgrounds' => config('presentation.backgrounds'),
            'fonts' => config('presentation.fonts'),
        ]);
    }

    public function update(Request $request, Schedule $schedule)
    {
        $themeColor = $request->theme_color ?? $schedule->theme_color ?? '#1b2735';

        $schedule->update([
            'theme' => $request->theme,
            'preacher_name' => $request->preacher_name,
            'theme_color' => $themeColor
        ]);

        if ($request->has('blocks')) {
            $liturgy = $schedule->liturgy;
            
            if (!str_contains($liturgy->name, '(Custom)')) {
                $liturgy = Liturgy::create([
                    'name' => ($request->theme ?? 'Jadwal') . ' (Custom)'
                ]);
                $schedule->update(['liturgy_id' => $liturgy->id]);
            } else {
                $liturgy->items()->delete();
            }

            $schedule->details()->delete();
            $schedule->customSlides()->delete();

            $this->processBlocks($request->blocks, $liturgy, $schedule);
        } 
        else {
            $schedule->details()->delete();
            $schedule->customSlides()->delete();
            $this->saveDetails($schedule, $request);
        }

        ActivityLog::record('jadwal.ubah', $schedule, 'Mengubah isi jadwal ' . $schedule->worship_date->translatedFormat('d F Y'));
        PresenterState::bumpContent($schedule->id);

        if ($request->ajax() || $request->wantsJson()) {
            $fresh = $schedule->fresh();

            return response()->json(['success' => true] + PresenterController::payload($fresh, app(SlideBuilder::class)));
        }

        if (str_contains(url()->previous(), 'kontrol')) {
            return redirect()->route('liturgy.control', $schedule->id)->with('success', 'Perubahan slide berhasil disimpan.');
        }

        return redirect()->route('liturgy.edit', $schedule->id)->with('success', 'Presentasi berhasil diperbarui.');
    }

    private function processBlocks($blocks, $liturgy, $schedule)
    {
        $order = 1;
        foreach (array_values($blocks) as $block) {
            $type = $block['type'] ?? 'polos';
            $title = $block['title'] ?? 'Slide';
            $content = $block['content'] ?? '';
            
            $useCamera = isset($block['use_camera']) ? filter_var($block['use_camera'], FILTER_VALIDATE_BOOLEAN) : false;
            $baitData = $block['bait'] ?? [];

            if ($type === 'nyanyian') {
                $itemTitle = 'Nyanyian: ' . ($title ?: 'Pujian');
                $filteredBait = [];
                foreach ($baitData as $key => $value) {
                    if (!is_null($value) && trim($value) !== '') {
                        $filteredBait[$key] = $value;
                    }
                }

                $dynContent = [
                    'judul' => $block['judul'] ?? $title,
                    'bait' => $filteredBait,
                    'use_camera' => $useCamera
                ];
            } elseif ($type === 'alkitab') {
                $itemTitle = 'Bacaan Alkitab: ' . ($title ?: 'Pelayanan Firman');
                $dynContent = [
                    'ref' => trim((string) ($block['ref'] ?? '')),
                    'content' => is_array($content) ? ($content[0] ?? '') : $content,
                    'use_camera' => $useCamera
                ];
            } else {
                $itemTitle = empty($title) ? 'Slide Bebas' : 'Slide Bebas: ' . $title;
                $dynContent = [
                    'custom_title' => $title,
                    'content' => is_array($content) ? ($content[0] ?? '') : $content,
                    'use_camera' => $useCamera
                ];
            }

            $item = $liturgy->items()->create([
                'title' => $itemTitle,
                'is_dynamic' => true,
                'order_number' => $order++,
            ]);

            ScheduleDetail::create([
                'schedule_id' => $schedule->id,
                'liturgy_item_id' => $item->id,
                'dynamic_content' => $dynContent
            ]);
        }
    }

    public function presentation(Request $request, Schedule $schedule, SlideBuilder $builder)
    {
        return view('liturgy.presentation', [
            'schedule' => $schedule,
            'payload' => PresenterController::payload($schedule, $builder),
            'remote' => $request->boolean('remote'),
        ]);
    }

    private function saveDetails($schedule, $request)
    {
        if(!$request->has('dynamic_content')) return;

        foreach ($request->dynamic_content as $itemId => $content) {
            if (empty($content)) continue;

            if (is_array($content)) {
                if (isset($content['bait'])) {
                    $filteredBait = [];
                    foreach ($content['bait'] as $k => $v) {
                        if (!is_null($v) && trim($v) !== '') $filteredBait[$k] = $v;
                    }
                    $content['bait'] = $filteredBait;
                }
                
                if (isset($content['use_camera'])) {
                    $content['use_camera'] = filter_var($content['use_camera'], FILTER_VALIDATE_BOOLEAN);
                } else {
                    $content['use_camera'] = false;
                }

                ScheduleDetail::create([
                    'schedule_id' => $schedule->id, 
                    'liturgy_item_id' => $itemId, 
                    'dynamic_content' => $content
                ]);
            } else {
                ScheduleDetail::create([
                    'schedule_id' => $schedule->id, 
                    'liturgy_item_id' => $itemId, 
                    'dynamic_content' => $content
                ]);
            }
        }

        if ($request->has('custom_slides')) {
            foreach ($request->custom_slides as $itemId => $slides) {
                foreach ($slides as $slide) {
                    if (!empty($slide['title']) && !empty($slide['content'])) {
                        // Lagu sisipan: isi JSON {judul, bait} disimpan apa adanya
                        if (($slide['kind'] ?? null) === ScheduleCustomSlide::KIND_SONG) {
                            if (is_array(json_decode((string) $slide['content'], true))) {
                                ScheduleCustomSlide::create([
                                    'schedule_id' => $schedule->id,
                                    'liturgy_item_id' => $itemId,
                                    'kind' => ScheduleCustomSlide::KIND_SONG,
                                    'title' => mb_substr((string) $slide['title'], 0, 250),
                                    'content' => (string) $slide['content'],
                                ]);
                            }
                            continue;
                        }

                        $contentStr = $slide['content'];
                        
                        // PERBAIKAN: Menggunakan penanda aman [KAMERA_AKTIF]
                        if (isset($slide['use_camera']) && filter_var($slide['use_camera'], FILTER_VALIDATE_BOOLEAN)) {
                            if (!str_contains($contentStr, '[KAMERA_AKTIF]')) {
                                $contentStr .= '[KAMERA_AKTIF]';
                            }
                        } else {
                            $contentStr = str_replace('[KAMERA_AKTIF]', '', $contentStr);
                        }

                        ScheduleCustomSlide::create([
                            'schedule_id' => $schedule->id, 
                            'liturgy_item_id' => $itemId, 
                            'title' => $slide['title'], 
                            'content' => $contentStr
                        ]);
                    }
                }
            }
        }
    }

    public function destroy(Schedule $schedule)
    {
        $schedule->delete();
        ActivityLog::record('jadwal.hapus', null, 'Menghapus jadwal ' . $schedule->worship_date->translatedFormat('d F Y'));
        return back()->with('success', 'Jadwal ibadah berhasil dihapus.');
    }

    public function fetchAlkitab(Request $request, Bible $bible)
    {
        try {
            $passage = $bible->passage((string) $request->query('q', ''));
        } catch (\InvalidArgumentException|\RuntimeException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }

        return response()->json([
            'success' => true,
            'reference' => $passage['reference'],
            'version' => $passage['version'],
            'count' => count($passage['verses']),
            'text' => $passage['text'],
        ]);
    }

    public function exportPdf(Schedule $schedule)
    {
        Carbon::setLocale('id');
        $scheduleDetails = $schedule->details()->get()->keyBy('liturgy_item_id');
        $liturgyItems = $schedule->liturgy->items;
        $announcements = Announcement::where('is_active', true)->orderBy('order_num')->get();

        return view('liturgy.pdf', compact('schedule', 'liturgyItems', 'scheduleDetails', 'announcements'));
    }
}