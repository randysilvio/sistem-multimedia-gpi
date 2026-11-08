<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Song extends Model
{
    use HasFactory;

    protected $fillable = ['book', 'number', 'title', 'verses', 'verse_numbers', 'chorus', 'is_incomplete'];

    // Mengubah JSON dari database kembali menjadi Array di Laravel
    protected $casts = [
        'verses' => 'array',
        'verse_numbers' => 'array',
        'is_incomplete' => 'boolean',
    ];

    /**
     * Cari lagu dari buku + nomor. Lagu tanpa nomor resmi boleh dicari dengan
     * sepenggal judulnya (mis. buku BEBAS, nomor "seka").
     */
    public static function findByRef(?string $book, ?string $number): ?self
    {
        $book = strtoupper(trim((string) $book));
        $number = trim((string) $number);
        if ($book === '' || $number === '') {
            return null;
        }
        $song = static::where('book', $book)->where('number', $number)->first();
        if (!$song && !ctype_digit($number) && mb_strlen($number) >= 3) {
            $song = static::where('book', $book)->where('title', 'like', '%' . $number . '%')->orderBy('title')->first();
        }

        return $song;
    }

    /**
     * Isi slide lagu untuk jadwal: ['judul' => ..., 'bait' => ['1' => ..., 'ref_1' => '[REFF]...', ...]].
     *
     * @param  array<int>|null  $verses  nomor bait yang dinyanyikan (null = semua)
     */
    public function slideContent(?array $verses = null): array
    {
        $all = $this->numberedVerses();
        $wanted = $verses ? array_values(array_filter($verses, fn ($n) => isset($all[(int) $n]))) : array_keys($all);
        if (!$wanted) {
            $wanted = array_keys($all);
        }
        $bait = [];
        foreach ($wanted as $n) {
            $bait[(string) $n] = trim($all[(int) $n]);
            if (!empty($this->chorus)) {
                $bait['ref_' . $n] = "[REFF]\n" . trim($this->chorus);
            }
        }

        return ['judul' => $this->displayTitle(), 'bait' => $bait];
    }

    /** Judul untuk slide: "KJ 21 - Hari Minggu…"; lagu kategori Lainnya cukup judulnya. */
    public function displayTitle(): string
    {
        if ($this->book === 'BEBAS' || $this->book === null || $this->book === '') {
            return $this->title;
        }

        return trim("{$this->book} {$this->number}") . ' - ' . $this->title;
    }

    /**
     * Bait dengan nomor aslinya, mis. [1 => '...', 7 => '...'].
     * Bila verse_numbers kosong/tidak cocok, bait dinomori 1, 2, 3, ...
     */
    public function numberedVerses(): array
    {
        $verses = array_values($this->verses ?? []);
        $numbers = array_values($this->verse_numbers ?? []);
        $useNumbers = count($numbers) === count($verses) && count(array_unique($numbers)) === count($numbers);

        $result = [];
        foreach ($verses as $i => $text) {
            $result[$useNumbers ? (int) $numbers[$i] : $i + 1] = $text;
        }

        return $result;
    }

    /**
     * Simpan nomor bait hanya bila tidak berurutan 1..n.
     *
     * @param  array<int, string|null>  $texts
     * @param  array<int, string|int|null>  $numbers
     * @return array{verses: array<int, string>, verse_numbers: array<int, int>|null}
     */
    public static function normalizeVerses(array $texts, array $numbers = []): array
    {
        $verses = [];
        $nums = [];
        foreach (array_values($texts) as $i => $text) {
            if ($text === null || trim($text) === '') {
                continue;
            }
            $verses[] = $text;
            $n = (int) ($numbers[$i] ?? 0);
            $nums[] = $n > 0 ? $n : (empty($nums) ? 1 : max($nums) + 1);
        }

        $sequential = $nums === range(1, max(1, count($nums))) || empty($nums);
        $unique = count(array_unique($nums)) === count($nums);

        return ['verses' => $verses, 'verse_numbers' => ($sequential || !$unique) ? null : $nums];
    }
}
