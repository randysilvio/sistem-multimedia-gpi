<?php

namespace App\Http\Controllers;

use App\Services\ActivityLog;
use Illuminate\Http\Request;
use App\Models\Song;

class SongController extends Controller
{
    // Menampilkan daftar lagu
    public function index(Request $request)
    {
        $query = Song::query();
        $search = trim((string) $request->query('search', ''));
        $book = strtoupper(trim((string) $request->query('buku', '')));

        if ($book !== '') {
            $query->where('book', $book);
        }
        if ($request->boolean('incomplete')) {
            $query->where('is_incomplete', true);
        }
        if ($search !== '') {
            // "KJ 21" / "nkb189" = buku + nomor; selain itu cari di judul, nomor, dan lirik
            if (preg_match('/^([a-z]{2,6})\s*\.?\s*(\d+[a-z]?)$/i', $search, $m)) {
                $query->where('book', strtoupper($m[1]))->where('number', $m[2]);
            } else {
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'LIKE', "%{$search}%")
                      ->orWhere('number', $search)
                      ->orWhere('verses', 'LIKE', "%{$search}%");
                });
            }
        }

        // Urut nomor sebagai angka (2, 14, 21, bukan 14, 2, 21)
        $numeric = Song::query()->getConnection()->getDriverName() === 'sqlite' ? 'CAST(number AS INTEGER)' : 'CAST(number AS UNSIGNED)';
        $songs = $query->orderBy('book')->orderByRaw($numeric)->orderBy('number')->paginate(25)->withQueryString();
        $books = Song::query()->select('book')->distinct()->orderBy('book')->pluck('book')->filter()->values();

        return view('songs.index', compact('songs', 'books', 'search', 'book'));
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

        // Bersihkan bait kosong & simpan nomor bait asli (mis. hanya bait 1 dan 7)
        $verses = Song::normalizeVerses($request->verses, (array) $request->input('verse_numbers', []));

        Song::create($verses + [
            'book' => strtoupper($request->book),
            'number' => $request->number,
            'title' => $request->title,
            'chorus' => $request->chorus,
            'is_incomplete' => $request->boolean('is_incomplete'),
        ]);

        ActivityLog::record('lagu.tambah', null, 'Menambah lagu ' . strtoupper((string) $request->book) . " {$request->number} - {$request->title}");
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
        $verses = Song::normalizeVerses($request->verses, (array) $request->input('verse_numbers', []));

        $song->update($verses + [
            'book' => strtoupper($request->book),
            'number' => $request->number,
            'title' => $request->title,
            'chorus' => $request->chorus,
            'is_incomplete' => $request->boolean('is_incomplete'),
        ]);

        ActivityLog::record('lagu.ubah', $song, 'Mengubah lagu ' . $song->displayTitle());
        return redirect()->route('songs.index')->with('success', 'Lagu berhasil diperbarui.');
    }

    // Hapus lagu
    public function destroy(Song $song)
    {
        $song->delete();
        ActivityLog::record('lagu.hapus', null, 'Menghapus lagu ' . $song->displayTitle());
        return redirect()->route('songs.index')->with('success', 'Lagu dihapus.');
    }

    // Tampilan cetak cantik
    public function print(Song $song)
    {
        return view('songs.print', compact('song'));
    }

    // Daftar ringkas semua lagu untuk kotak "Pilih Lagu" di builder & halaman jadwal
    public function apiList()
    {
        $order = ['KJ', 'NKB', 'PKJ', 'NR', 'DSL', 'GPM', 'BEBAS'];

        $songs = Song::query()->get(['book', 'number', 'title', 'is_incomplete'])
            ->sortBy(fn (Song $s) => [
                array_search($s->book, $order, true) === false ? 99 : array_search($s->book, $order, true),
                (int) $s->number,
                (string) $s->number,
            ])
            ->values()
            ->map(fn (Song $s) => [
                'book' => $s->book ?: 'BEBAS',
                'number' => (string) $s->number,
                'title' => $s->title,
                'incomplete' => (bool) $s->is_incomplete,
            ]);

        return response()->json(['songs' => $songs]);
    }

    // API Untuk Ditarik oleh Control Panel / Builder
    public function apiFetch(Request $request)
    {
        $book = strtoupper($request->query('buku'));
        $number = $request->query('nomor');

        $song = Song::findByRef($book, $number);

        if (!$song) {
            return response()->json(['success' => false, 'message' => "Lagu $book No. $number tidak ditemukan di database Anda."]);
        }

        // Potongan teks dipisah ===SLIDE_BREAK===; `labels` sejajar dengan potongan itu
        // (nomor bait asli, atau 'Reff') agar form tidak salah menomori bait.
        $pieces = [];
        $labels = [];
        foreach ($song->numberedVerses() as $num => $verse) {
            $pieces[] = trim($verse);
            $labels[] = (string) $num;
            if (!empty($song->chorus)) {
                $pieces[] = "[REFF]\n" . trim($song->chorus);
                $labels[] = 'Reff';
            }
        }

        return response()->json([
            'success' => true,
            'judul' => $song->displayTitle(),
            'text' => implode("\n===SLIDE_BREAK===\n", $pieces),
            'labels' => $labels,
        ]);
    }
}