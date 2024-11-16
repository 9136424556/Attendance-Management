<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Break_time extends Model
{
    use HasFactory;

    protected $fillable = [
        'attendance_id',
        'break_start_time',
        'break_end_time',
    ];

   // これにより、break_start_time と break_end_time が自動的に Carbon インスタンスに変換されます
    protected $dates = ['break_start_time', 'break_end_time'];

    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }

}
