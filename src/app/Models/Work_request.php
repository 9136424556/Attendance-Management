<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Work_request extends Model
{
    use HasFactory;

    protected $fillable = [
        'attendance_id',
        'user_id',
        'work_date',
        'start_time',
        'end_time',
        'break_start_time',
        'break_end_time',
        'reason',
        'status',
        'requested_at',
        'approval_at',
        'is_submitted',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }
}
