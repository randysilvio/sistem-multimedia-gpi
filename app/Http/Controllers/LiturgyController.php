<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Liturgy;
use App\Models\Schedule;
use App\Models\ScheduleDetail;
use App\Models\ScheduleCustomSlide;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;

class LiturgyController extends Controller
{
    /**
     * Menampilkan daftar jadwal ibadah (Galeri ala Google Docs)
     */
    public function gallery()
    {
        Carbon::setLocale('id');
        // Tarik template liturgi untuk menu atas (Google Docs style)
        $liturgies = Liturgy::orderBy('id')->get(); 
        $schedules = Schedule::with('liturgy')->orderBy('worship_date', 'desc')->get();
        
        return view('liturgy.gallery', compact('schedules', 'liturgies'));
    }

    /**
     * Form pembuatan jadwal baru (Template Baku)
     */
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

    /**
     * Menampilkan Form Builder (Kanvas Kosong ala Google Form)
     */
    public function builder()
    {
        return view('liturgy.builder');
    }

    /**
     * Menyimpan jadwal dari form template baku
     */
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
            'theme_color' => $request->theme_color ?? '#198754'
        ]);

        $this->saveDetails($schedule, $request);

        return redirect()->route('liturgy.edit', $schedule->id)
                         ->with('success', 'Jadwal berhasil dibuat. Selamat melayani!');
    }

    /**
     * Menyimpan presentasi kustom dari Form Builder
     */
    public function storeCustom(Request $request)
    {
        $request->validate([
            'schedule_name' => 'required|string',
            'worship_date' => 'required|date',
            'blocks' => 'required|array'
        ]);

        // 1. Buat Template Liturgi Kustom On-The-Fly
        $liturgy = Liturgy::create([
            'name' => $request->schedule_name . ' (Custom)'
        ]);

        // 2. Buat Jadwal Ibadah
        $schedule = Schedule::create([
            'liturgy_id' => $liturgy->id,
            'worship_date' => $request->worship_date,
            'theme' => $request->schedule_name,
            'preacher_name' => $request->preacher_name,
            'theme_color' => $request->theme_color ?? '#1b2735'
        ]);

        // 3. Proses setiap blok dari Builder menjadi Item Liturgi & Detail
        $order = 1;
        foreach ($request->blocks as $block) {
            $type = $block['type'] ?? 'polos';
            $title = $block['title'] ?? 'Slide';
            $content = $block['content'] ?? '';

            // Trik: Beri prefix pada title agar Control Panel mengenali jenis inputnya
            if ($type === 'nyanyian') {
                $itemTitle = 'Nyanyian: ' . $title;
                $dynContent = [
                    'judul' => $title,
                    'bait' => is_array($content) ? array_filter($content, fn($v) => !is_null($v) && trim($v) !== '') : []
                ];
            } elseif ($type === 'alkitab') {
                $itemTitle = 'Bacaan Alkitab: ' . $title;
                $dynContent = is_array($content) ? ($content[0] ?? '') : $content;
            } elseif ($type === 'votum') {
                $itemTitle = 'Prosesi: ' . $title;
                $dynContent = [
                    'custom_title' => $title,
                    'content' => is_array($content) ? ($content[0] ?? '') : $content
                ];
            } elseif ($type === 'aksi') {
                $itemTitle = 'Sikap Jemaat';
                $dynContent = is_array($content) ? ($content[0] ?? '') : $content;
            } else {
                $itemTitle = 'Slide Bebas: ' . $title;
                $dynContent = is_array($content) ? ($content[0] ?? '') : $content;
            }

            // Buat Item Struktur
            $item = $liturgy->items()->create([
                'title' => $itemTitle,
                'is_dynamic' => true,
                'order_number' => $order++,
            ]);

            // Simpan Teksnya
            ScheduleDetail::create([
                'schedule_id' => $schedule->id,
                'liturgy_item_id' => $item->id,
                'dynamic_content' => $dynContent
            ]);
        }

        return redirect()->route('liturgy.edit', $schedule->id)
                         ->with('success', 'Presentasi Kustom berhasil dirakit!');
    }

    /**
     * Menampilkan Control Panel (Menggantikan halaman edit lama)
     */
    public function edit(Schedule $schedule)
    {
        $schedule->load(['details', 'customSlides', 'liturgy.items']);
        return view('liturgy.control_panel', compact('schedule'));
    }

    /**
     * Update Live: Menyimpan perubahan dari Control Panel secara async (AJAX)
     */
    public function update(Request $request, Schedule $schedule)
    {
        $schedule->update([
            'theme' => $request->theme,
            'preacher_name' => $request->preacher_name,
            'theme_color' => $request->theme_color
        ]);

        $schedule->details()->delete();
        $schedule->customSlides()->delete();
        
        $this->saveDetails($schedule, $request);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->route('liturgy.edit', $schedule->id)->with('success', 'Data diperbarui.');
    }

    /**
     * Menampilkan Output Proyektor (Jendela terpisah)
     */
    public function presentation(Schedule $schedule)
    {
        Carbon::setLocale('id');
        $scheduleDetails = $schedule->details()->get()->keyBy('liturgy_item_id');
        $liturgyItems = $schedule->liturgy->items;
        $customSlides = $schedule->customSlides()->get()->groupBy('liturgy_item_id');
        
        return view('liturgy.presentation', compact('schedule', 'liturgyItems', 'scheduleDetails', 'customSlides'));
    }

    /**
     * Helper untuk menyimpan detail konten dinamis
     */
    private function saveDetails($schedule, $request)
    {
        if(!$request->has('dynamic_content')) return;

        foreach ($request->dynamic_content as $itemId => $content) {
            if (empty($content)) continue;

            if (is_array($content)) {
                if (isset($content['bait'])) {
                    $content['bait'] = array_filter($content['bait'], fn($v) => !is_null($v) && $v !== '');
                }
                
                if (!empty($content['judul']) || !empty($content['bait']) || !empty($content['content']) || !empty($content['custom_title'])) {
                    ScheduleDetail::create([
                        'schedule_id' => $schedule->id, 
                        'liturgy_item_id' => $itemId, 
                        'dynamic_content' => $content
                    ]);
                }
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

    /**
     * Menghapus jadwal
     */
    public function destroy(Schedule $schedule)
    {
        $schedule->delete();
        return back()->with('success', 'Jadwal ibadah berhasil dihapus.');
    }

    /**
     * Mengambil ayat Alkitab dari API
     */
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

    /**
     * Menampilkan versi PDF
     */
    public function exportPdf(Schedule $schedule)
    {
        Carbon::setLocale('id');
        $scheduleDetails = $schedule->details()->get()->keyBy('liturgy_item_id');
        $liturgyItems = $schedule->liturgy->items;
        return view('liturgy.pdf', compact('schedule', 'liturgyItems', 'scheduleDetails'));
    }
}