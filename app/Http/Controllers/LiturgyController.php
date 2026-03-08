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
                        
                        // Menangani input manual use_camera dari custom slide (hack fallback)
                        $contentStr = $slide['content'];
                        if (isset($slide['use_camera']) && filter_var($slide['use_camera'], FILTER_VALIDATE_BOOLEAN)) {
                            if (!str_contains($contentStr, '')) {
                                $contentStr .= '';
                            }
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
        return back()->with('success', 'Jadwal ibadah berhasil dihapus.');
    }

    public function fetchAlkitab(Request $request)
    {
        $query = $request->query('q');
        if (!$query) return response()->json(['success' => false, 'message' => 'Query kosong.']);

        $url = "https://beeapi.lionbat.com/search.json?q=" . urlencode($query);
        
        try {
            // Tambahkan withoutVerifying() untuk bypass masalah SSL & naikkan timeout jadi 15 detik
            $response = Http::withoutVerifying()->timeout(15)->get($url);
            
            if ($response->successful()) {
                $data = $response->json();
                
                // Pastikan data yang ditarik valid
                if (isset($data['data']) && is_array($data['data'])) {
                    if (count($data['data']) == 0) {
                        return response()->json(['success' => false, 'message' => 'Ayat tidak ditemukan. Pastikan format benar (contoh: Yohanes 3:16)']);
                    }

                    $text = "";
                    foreach ($data['data'] as $item) {
                        // Mengamankan teks dari tag HTML yang mungkin terbawa dari API
                        $text .= $item['verse'] . ". " . strip_tags($item['text']) . "\n";
                    }
                    
                    return response()->json(['success' => true, 'text' => trim($text)]);
                }
                
                return response()->json(['success' => false, 'message' => 'Server Alkitab merespons, namun format data tidak sesuai.']);
            }
            
            return response()->json(['success' => false, 'message' => 'Server Alkitab sedang sibuk. Status code: ' . $response->status()]);
            
        } catch (\Exception $e) {
            // Menampilkan pesan error spesifik agar kita tahu pasti apa masalahnya
            $errMsg = $e->getMessage();
            
            // Menyederhanakan pesan error cURL yang panjang
            if (str_contains($errMsg, 'cURL error 28')) {
                $errMsg = "Koneksi ke server terputus (Timeout). Coba lagi.";
            } elseif (str_contains($errMsg, 'cURL error 6') || str_contains($errMsg, 'Could not resolve host')) {
                $errMsg = "Tidak ada koneksi internet pada server/komputer Anda.";
            }
            
            return response()->json(['success' => false, 'message' => 'Gagal: ' . $errMsg]);
        }
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