<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Song extends Model
{
    use HasFactory;

    protected $fillable = ['book', 'number', 'title', 'verses', 'chorus'];

    // Mengubah JSON dari database kembali menjadi Array di Laravel
    protected $casts = [
        'verses' => 'array',
    ];
}