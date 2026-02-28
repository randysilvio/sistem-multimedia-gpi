<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Song;

class SongController extends Controller
{
    // Menampilkan daftar lagu
    public function index(Request $request)
    {
        $query = Song::query();
        if ($request->has('search')) {
            $search = $request->search;
            $query->where('title', 'LIKE', "%{$search}%")
                  ->orWhere('number', 'LIKE', "%{$search}%");
        }
        $songs = $query->orderBy('book')->orderBy('number')->paginate(20);
        return view('songs.index', compact('songs'));
    }

    // Form tambah lagu
    public function create()
    {
        return view('songs.form');
    }

    // Simpan lagu baru
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'verses' => 'required|array',
        ]);

        // Bersihkan array bait dari yang kosong
        $verses = array_filter($request->verses, fn($v) => !is_null($v) && trim($v) !== '');

        Song::create([
            'book' => strtoupper($request->book),
            'number' => $request->number,
            'title' => $request->title,
            'verses' => array_values($verses),
            'chorus' => $request->chorus,
        ]);

        return redirect()->route('songs.index')->with('success', 'Lagu berhasil ditambahkan ke database.');
    }

    // Form edit lagu
    public function edit(Song $song)
    {
        return view('songs.form', compact('song'));
    }

    // Update lagu
    public function update(Request $request, Song $song)
    {
        $request->validate(['title' => 'required', 'verses' => 'required|array']);
        $verses = array_filter($request->verses, fn($v) => !is_null($v) && trim($v) !== '');

        $song->update([
            'book' => strtoupper($request->book),
            'number' => $request->number,
            'title' => $request->title,
            'verses' => array_values($verses),
            'chorus' => $request->chorus,
        ]);

        return redirect()->route('songs.index')->with('success', 'Lagu berhasil diperbarui.');
    }

    // Hapus lagu
    public function destroy(Song $song)
    {
        $song->delete();
        return redirect()->route('songs.index')->with('success', 'Lagu dihapus.');
    }

    // Tampilan cetak cantik
    public function print(Song $song)
    {
        return view('songs.print', compact('song'));
    }

    // API Untuk Ditarik oleh Control Panel / Builder
    public function apiFetch(Request $request)
    {
        $book = strtoupper($request->query('buku'));
        $number = $request->query('nomor');

        $song = Song::where('book', $book)->where('number', $number)->first();

        if (!$song) {
            return response()->json(['success' => false, 'message' => "Lagu $book No. $number tidak ditemukan di database Anda."]);
        }

        $text = "";
        foreach ($song->verses as $index => $verse) {
            $text .= trim($verse) . "\n";
            
            // Jika ada Reff, sisipkan Reff setelah setiap bait
            if (!empty($song->chorus)) {
                $text .= "===SLIDE_BREAK===\n[REFF]\n" . trim($song->chorus) . "\n";
            }

            // Pemisah ke bait berikutnya
            if ($index < count($song->verses) - 1) {
                $text .= "===SLIDE_BREAK===\n";
            }
        }

        return response()->json([
            'success' => true,
            'judul' => $song->book . " " . $song->number . " - " . $song->title,
            'text' => trim($text)
        ]);
    }
}