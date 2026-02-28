<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScheduleDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'schedule_id',
        'liturgy_item_id',
        'dynamic_content'
    ];

    // Tambahkan baris ini agar Laravel otomatis mengkonversi array lagu menjadi teks JSON dan sebaliknya
    protected $casts = [
        'dynamic_content' => 'array',
    ];

    public function schedule()
    {
        return $this->belongsTo(Schedule::class);
    }

    public function liturgyItem()
    {
        return $this->belongsTo(LiturgyItem::class);
    }
}