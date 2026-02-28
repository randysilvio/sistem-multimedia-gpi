<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Liturgy extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 
        'description',
        'default_color'
    ];

    public function items()
    {
        return $this->hasMany(LiturgyItem::class)->orderBy('order_number');
    }
    
    public function schedules()
    {
        return $this->hasMany(Schedule::class);
    }
}