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
            $title = $block['title'] ?? 'Slide';
            $content = $block['content'] ?? '';
            
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

    // =========================================================================
    // PEMBARUAN: Sistem Ganda (Dual-Engine) Tarik Data Alkitab (Super Stabil)
    // =========================================================================
    public function fetchAlkitab(Request $request)
    {
        $query = trim($request->query('q'));
        if (!$query) return response()->json(['success' => false, 'message' => 'Query kosong.']);

        // PENTING: Bersihkan spasi di sekitar titik dua dan strip agar API SABDA tidak error
        // Contoh: "Yohanes 3 : 16 - 18" otomatis dirapikan menjadi "Yohanes 3:16-18"
        $cleanQuery = preg_replace('/\s*:\s*/', ':', $query);
        $cleanQuery = preg_replace('/\s*-\s*/', '-', $cleanQuery);

        // --- ENGINE 1: Mencoba ambil data dari API Resmi SABDA (XML) ---
        $urlXml = "https://alkitab.sabda.org/api/passage.php?passage=" . urlencode($cleanQuery);
        
        try {
            $response = Http::withoutVerifying()->timeout(15)->get($urlXml);
            
            if ($response->successful()) {
                $xmlString = trim($response->body());
                libxml_use_internal_errors(true);
                $xml = simplexml_load_string($xmlString);
                
                if ($xml !== false && isset($xml->book)) {
                    $text = "";
                    foreach ($xml->book as $book) {
                        foreach ($book->chapter as $chapter) {
                            foreach ($chapter->verse as $verse) {
                                $verseNum = (string) $verse['number'];
                                
                                // Ambil teks apapun bentuk node XML-nya
                                $verseText = (string) $verse;
                                if (isset($verse->text)) {
                                    $verseText = (string) $verse->text;
                                }
                                if (empty(trim($verseText))) {
                                    $verseText = strip_tags($verse->asXML());
                                }
                                
                                // Bersihkan spasi berlebih
                                $verseText = preg_replace('/\s+/', ' ', trim($verseText));
                                
                                if (!empty($verseText) && !preg_match('/^[0-9]+$/', $verseText)) {
                                    $text .= $verseNum . ". " . $verseText . "\n";
                                }
                            }
                        }
                    }

                    if (!empty(trim($text))) {
                        return response()->json(['success' => true, 'text' => trim($text)]);
                    }
                }
            }
        } catch (\Exception $e) {
            // Jika Engine 1 gagal, abaikan dan lanjut ke Engine 2
        }

        // --- ENGINE 2 (FALLBACK): Menggunakan Scraper HTML ke alkitab.mobi ---
        $urlHtml = "https://alkitab.mobi/tb/search?q=" . urlencode($cleanQuery);
        
        try {
            $responseHtml = Http::withoutVerifying()->timeout(15)->get($urlHtml);
            
            if ($responseHtml->successful()) {
                $html = $responseHtml->body();
                
                // Skenario A: Hasil pencarian menampilkan list ayat 
                preg_match_all('/<span class="ref"><a[^>]*>(.*?)<\/a><\/span>\s*(.*?)<\/p>/is', $html, $matches);
                if (!empty($matches[1]) && !empty($matches[2])) {
                    $text = "";
                    foreach ($matches[1] as $idx => $ref) {
                        $refParts = explode(':', trim(strip_tags($ref)));
                        $verseNum = end($refParts);
                        $ayatText = preg_replace('/\s+/', ' ', trim(strip_tags($matches[2][$idx])));
                        $text .= trim($verseNum) . ". " . $ayatText . "\n";
                    }
                    if (!empty(trim($text))) return response()->json(['success' => true, 'text' => trim($text)]);
                }
                
                // Skenario B: Hasil pencarian me-redirect ke halaman pasal utuh 
                preg_match_all('/<div[^>]*class="v"[^>]*>.*?<span[^>]*class="v"[^>]*>.*?<b>(\d+)<\/b>.*?<\/span>\s*(.*?)<\/div>/is', $html, $matchesB);
                if (!empty($matchesB[1]) && !empty($matchesB[2])) {
                    $text = "";
                    foreach ($matchesB[1] as $idx => $verseNum) {
                        $ayatText = preg_replace('/\s+/', ' ', trim(strip_tags($matchesB[2][$idx])));
                        $text .= trim($verseNum) . ". " . $ayatText . "\n";
                    }
                    if (!empty(trim($text))) return response()->json(['success' => true, 'text' => trim($text)]);
                }
            }
        } catch (\Exception $e) {}

        // Jika kedua engine gagal menemukan data
        return response()->json(['success' => false, 'message' => 'Gagal menarik ayat. Pastikan penulisan benar (Contoh: Yohanes 3:16) atau periksa koneksi internet Anda.']);
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