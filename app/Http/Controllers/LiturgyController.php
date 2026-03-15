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
        return back()->with('success', 'Jadwal ibadah berhasil dihapus.');
    }

    public function fetchAlkitab(Request $request)
    {
        $query = strtolower(trim($request->query('q')));
        if (!$query) return response()->json(['success' => false, 'message' => 'Silakan masukkan ayat.']);

        $splitColon = explode(':', $query);
        if (count($splitColon) !== 2) {
            return response()->json(['success' => false, 'message' => 'Format salah! Gunakan titik dua (:). Contoh: Yohanes 3:16']);
        }

        $leftSide = trim($splitColon[0]);
        $rightSide = trim($splitColon[1]);

        $lastSpacePos = strrpos($leftSide, ' ');
        if ($lastSpacePos === false) {
            return response()->json(['success' => false, 'message' => 'Format salah! Beri spasi antara kitab dan pasal. Contoh: Yohanes 3:16']);
        }

        $bookName = trim(substr($leftSide, 0, $lastSpacePos));
        $chapter = trim(substr($leftSide, $lastSpacePos + 1));
        
        $verseParts = explode('-', $rightSide);
        $startVerse = (int)trim($verseParts[0]);
        $endVerse = isset($verseParts[1]) ? (int)trim($verseParts[1]) : $startVerse;

        $books = [
            'kejadian' => 1, 'kej' => 1, 'keluaran' => 2, 'kel' => 2, 'imamat' => 3, 'ima' => 3,
            'bilangan' => 4, 'bil' => 4, 'ulangan' => 5, 'ula' => 5, 'yosua' => 6, 'yos' => 6,
            'hakim-hakim' => 7, 'hakim' => 7, 'hak' => 7, 'rut' => 8, '1 samuel' => 9, '1samuel' => 9, '1 sam' => 9,
            '2 samuel' => 10, '2samuel' => 10, '2 sam' => 10, '1 raja-raja' => 11, '1 raja' => 11, '1raj' => 11, '1 raj' => 11,
            '2 raja-raja' => 12, '2 raja' => 12, '2raj' => 12, '2 raj' => 12, '1 tawarikh' => 13, '1tawarikh' => 13, '1 taw' => 13,
            '2 tawarikh' => 14, '2tawarikh' => 14, '2 taw' => 14, 'ezra' => 15, 'ezr' => 15, 'nehemia' => 16, 'neh' => 16,
            'ester' => 17, 'est' => 17, 'ayub' => 18, 'ayu' => 18, 'mazmur' => 19, 'maz' => 19, 'amsal' => 20, 'ams' => 20,
            'pengkhotbah' => 21, 'peng' => 21, 'kidung agung' => 22, 'kidung' => 22, 'kid' => 22, 'yesaya' => 23, 'yes' => 23,
            'yeremia' => 24, 'yer' => 24, 'ratapan' => 25, 'rat' => 25, 'yehezkiel' => 26, 'yeh' => 26, 'daniel' => 27, 'dan' => 27,
            'hosea' => 28, 'hos' => 28, 'yoel' => 29, 'yoe' => 29, 'amos' => 30, 'amo' => 30, 'obaja' => 31, 'oba' => 31,
            'yunus' => 32, 'yun' => 32, 'mikha' => 33, 'mik' => 33, 'nahum' => 34, 'nah' => 34, 'habakuk' => 35, 'hab' => 35,
            'zefanya' => 36, 'zef' => 36, 'hagai' => 37, 'hag' => 37, 'zakharia' => 38, 'zak' => 38, 'maleakhi' => 39, 'mal' => 39,
            'matius' => 40, 'mat' => 40, 'markus' => 41, 'mar' => 41, 'mrk' => 41, 'lukas' => 42, 'luk' => 42,
            'yohanes' => 43, 'yoh' => 43, 'kisah para rasul' => 44, 'kisah' => 44, 'kis' => 44, 'roma' => 45, 'rom' => 45,
            '1 korintus' => 46, '1 kor' => 46, '1kor' => 46, '2 korintus' => 47, '2 kor' => 47, '2kor' => 47,
            'galatia' => 48, 'gal' => 48, 'efesus' => 49, 'efe' => 49, 'filipi' => 50, 'fil' => 50, 'kolose' => 51, 'kol' => 51,
            '1 tesalonika' => 52, '1 tes' => 52, '1tes' => 52, '2 tesalonika' => 53, '2 tes' => 53, '2tes' => 53,
            '1 timotius' => 54, '1 tim' => 54, '1tim' => 54, '2 timotius' => 55, '2 tim' => 55, '2tim' => 55,
            'titus' => 56, 'tit' => 56, 'filemon' => 57, 'flm' => 57, 'ibrani' => 58, 'ibr' => 58, 'yakobus' => 59, 'yak' => 59,
            '1 petrus' => 60, '1 pet' => 60, '1pet' => 60, '2 petrus' => 61, '2 pet' => 61, '2pet' => 61,
            '1 yohanes' => 62, '1 yoh' => 62, '1yoh' => 62, '2 yohanes' => 63, '2 yoh' => 63, '2yoh' => 63,
            '3 yohanes' => 64, '3 yoh' => 64, '3yoh' => 64, 'yudas' => 65, 'yud' => 65, 'wahyu' => 66, 'wah' => 66
        ];

        if (!isset($books[$bookName])) {
            return response()->json(['success' => false, 'message' => "Kitab '{$bookName}' tidak ditemukan dalam sistem. Cek ejaan Anda."]);
        }

        $bookId = $books[$bookName];

        $url = "https://www.wordproject.org/bibles/id/{$bookId}/{$chapter}.htm";
        
        try {
            $response = Http::withoutVerifying()->timeout(15)->get($url);
            
            if ($response->successful()) {
                $html = $response->body();
                
                $parts = explode('<span class="verse" id="', $html);
                $chapterVerses = [];
                
                foreach($parts as $idx => $part) {
                    if ($idx == 0) continue;
                    
                    $explodeBracket = explode('>', $part, 2);
                    if (count($explodeBracket) < 2) continue;
                    
                    $idPart = $explodeBracket[0];
                    $textPart = $explodeBracket[1];
                    
                    $verseNum = (int) str_replace('"', '', $idPart);
                    
                    $textPart = preg_replace('/^\d+\s*<\/span>/', '', $textPart); 
                    $cleanText = trim(strip_tags($textPart));
                    $chapterVerses[$verseNum] = preg_replace('/\s+/', ' ', $cleanText);
                }

                $text = "";
                for ($v = $startVerse; $v <= $endVerse; $v++) {
                    if (isset($chapterVerses[$v])) {
                        $text .= $v . ". " . $chapterVerses[$v] . "\n";
                    }
                }
                
                if (!empty(trim($text))) {
                    return response()->json(['success' => true, 'text' => trim($text)]);
                } else {
                    return response()->json(['success' => false, 'message' => "Ayat {$startVerse}-{$endVerse} tidak ditemukan di pasal {$chapter}."]);
                }
            } else {
                 return response()->json(['success' => false, 'message' => "Server pangkalan data Alkitab sedang sibuk (Status: " . $response->status() . "). Coba beberapa saat lagi."]);
            }
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => "Koneksi ke pangkalan data terputus. Pastikan internet Anda aktif."]);
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