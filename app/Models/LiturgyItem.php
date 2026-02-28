<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LiturgyItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'liturgy_id',
        'order_number',
        'title',
        'is_dynamic',
        'static_content',
        'placeholder_tag',
        'congregation_action' // Tambahan baru
    ];

    protected $casts = [
        'is_dynamic' => 'boolean',
    ];

    public function liturgy()
    {
        return $this->belongsTo(Liturgy::class);
    }
    
    public function scheduleDetails()
    {
        return $this->hasMany(ScheduleDetail::class);
    }
}