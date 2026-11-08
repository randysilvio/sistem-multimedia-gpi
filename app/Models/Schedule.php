<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'liturgy_id',
        'worship_date',
        'theme',
        'preacher_name',
        'theme_color',
        'display_settings',
    ];

    protected $casts = [
        'worship_date' => 'date',
        'display_settings' => 'array',
    ];

    public function liturgy()
    {
        return $this->belongsTo(Liturgy::class);
    }

    public function details()
    {
        return $this->hasMany(ScheduleDetail::class);
    }

    public function customSlides() {
        return $this->hasMany(ScheduleCustomSlide::class);
    }
}