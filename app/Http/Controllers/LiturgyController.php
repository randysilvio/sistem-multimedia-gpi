<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Liturgy;
use App\Models\Schedule;
use App\Models\ScheduleDetail;
use App\Models\ScheduleCustomSlide;
use App\Models\Announcement; 
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;

class LiturgyController extends Controller
{
    public function gallery()
    {
        Carbon::setLocale('id');
        $liturgies = Liturgy::orderBy('id')->get(); 
        $schedules = Schedule::with('liturgy')->orderBy('worship_date', 'desc')->get();
        
        return view('liturgy.gallery', compact('schedules', 'liturgies'));
    }

    public function create(Request $request)
    {
        $liturgies = Liturgy::orderBy('id')->get();
        if ($liturgies->isEmpty()) {
            abort(404, 'Data Liturgi belum ada. Silakan jalankan seeder.');
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
            'theme_color' => $request->theme_color ?? '#1b2735'
        ]);

        $this->saveDetails($schedule, $request);

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
            'theme_color' => $request->theme_color ?? '#1b2735'
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

    public function controlPanel(Schedule $schedule)
    {
        $schedule->load(['details', 'customSlides', 'liturgy.items']);
        $announcements = Announcement::where('is_active', true)->orderBy('order_num')->get();
        
        return view('liturgy.control_panel', compact('schedule', 'announcements'));
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

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true]);
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
            $title = $block['title'] ?? '';
            $content = $block['content'] ?? '';
            
            // Tangkap status Kamera
            $useCamera = isset($block['use_camera']) ? true : false;
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
                    'content' => is_array($content) ? ($content[0] ?? '') : $content,
                    'use_camera' => $useCamera
                ];
            } else {
                // Teks Bebas (Bisa digunakan sebagai Sikap/Instruksi jika title kosong)
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

    public function presentation(Schedule $schedule)
    {
        Carbon::setLocale('id');
        $scheduleDetails = $schedule->details()->get()->keyBy('liturgy_item_id');
        $liturgyItems = $schedule->liturgy->items;
        $customSlides = $schedule->customSlides()->get()->groupBy('liturgy_item_id');
        $announcements = Announcement::where('is_active', true)->orderBy('order_num')->get();
        
        return view('liturgy.presentation', compact('schedule', 'liturgyItems', 'scheduleDetails', 'customSlides', 'announcements'));
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
                
                // Pastikan checkbox diconvert jadi boolean untuk konsistensi
                if (isset($content['use_camera'])) {
                    $content['use_camera'] = filter_var($content['use_camera'], FILTER_VALIDATE_BOOLEAN);
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
                        ScheduleCustomSlide::create([
                            'schedule_id' => $schedule->id, 
                            'liturgy_item_id' => $itemId, 
                            'title' => $slide['title'], 
                            'content' => $slide['content']
                        ]);
                    }
                }
            }
        }
    }

    public function destroy(Schedule $schedule)
    {
        $schedule->delete();
        return back()->with('success', 'Jadwal ibadah berhasil dihapus.');
    }

    public function fetchAlkitab(Request $request)
    {
        $query = $request->query('q');
        if (!$query) return response()->json(['success' => false, 'message' => 'Query kosong.']);

        $url = "https://beeapi.lionbat.com/search.json?q=" . urlencode($query);
        
        try {
            $response = Http::timeout(10)->get($url);
            if ($response->successful() && isset($response->json()['data'])) {
                $text = "";
                $data = $response->json()['data'];
                
                if(count($data) == 0) return response()->json(['success' => false, 'message' => 'Ayat tidak ditemukan.']);

                foreach ($data as $item) {
                    $text .= $item['verse'] . ". " . strip_tags($item['text']) . "\n";
                }
                
                return response()->json(['success' => true, 'text' => trim($text)]);
            }
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal menghubungi server Alkitab.']);
        }

        return response()->json(['success' => false, 'message' => 'Terjadi kesalahan sistem.']);
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