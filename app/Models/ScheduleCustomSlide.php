<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScheduleCustomSlide extends Model
{
    use HasFactory;

    public const KIND_SONG = 'song';

    protected $fillable = ['schedule_id', 'liturgy_item_id', 'kind', 'title', 'content'];

    public function schedule()
    {
        return $this->belongsTo(Schedule::class);
    }

    public function isSong(): bool
    {
        return $this->kind === self::KIND_SONG;
    }

    /** Isi lagu sisipan: ['judul' => ..., 'bait' => [...]] */
    public function songData(): array
    {
        $data = json_decode((string) $this->content, true);

        return is_array($data) ? $data + ['judul' => $this->title, 'bait' => []] : ['judul' => $this->title, 'bait' => []];
    }
}
