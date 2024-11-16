<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
       'user_id',
       'work_date',
       'start_time',
       'end_time',
       'overtime',
       'work_ontime',
    ];

    protected $dates = ['start_time', 'end_time'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

     // BreakTimeモデルとのリレーション
    public function breakTimes() 
    {
        return $this->hasMany(Break_time::class, 'attendance_id');
    }

    public function workRequest()
    {
        return $this->belongsTo(Work_request::class)->where('status', '承認待ち'); // '承認待ち'は「pending approval」の意味
    }

    // 合計休憩時間を計算するメソッド（分単位）
    public function TotalBreaktime()
    {
         // すべての休憩レコードを取得し、各休憩の時間差を合計
         return $this->breakTimes->reduce(function ($carry, $break) {
            // `break_start_time`と`break_end_time`がnullでない場合のみ計算
            if ($break->break_start_time && $break->break_end_time) {
                $breakDuration = $break->break_start_time->diffInMinutes($break->break_end_time);
                return $carry + $breakDuration;
            }
             // どちらかがnullの場合は、前回の合計をそのまま返す
            return $carry;
        }, 0);
    }

    public function calculateTotalWorkTime()
    {
        // 勤務開始時間と終了時間の差（分単位）
        $workDuration = $this->start_time->diffInMinutes($this->end_time);

        // 休憩時間の合計を取得
        $totalBreakMinutes = $this->TotalBreaktime();

        // 勤務時間の合計（分単位） = 勤務時間 - 休憩時間
        $totalWorkMinutes = $workDuration - $totalBreakMinutes;

        // 時間:分形式にフォーマットして返す
        return $this->formatTime($totalWorkMinutes);
    }

    // 時間:分形式にフォーマットするメソッド
    public function formatTime($totalMinutes)
    {
        $hours = floor($totalMinutes / 60);
        $minutes = $totalMinutes % 60;
        return sprintf('%d:%02d', $hours, $minutes);
    }
}
