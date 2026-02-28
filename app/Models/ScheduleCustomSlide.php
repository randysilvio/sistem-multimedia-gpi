<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScheduleCustomSlide extends Model {
    use HasFactory;
    protected $fillable = ['schedule_id', 'liturgy_item_id', 'title', 'content'];

    public function schedule() {
        return $this->belongsTo(Schedule::class);
    }
}