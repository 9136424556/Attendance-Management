<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Attendance;
use Carbon\Carbon;

class AttendancesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // ダミー出勤データを作成（factoryを使って）
        $attendances = Attendance::factory(5)->create()->each(function ($attendance) {
             // start_time を文字列に変換し、フォーマットを整える
            $startTimeRaw = $attendance->start_time;

            if ($startTimeRaw instanceof Carbon) {
                $startTime = $startTimeRaw->format('H:i'); // Carbon -> 'H:i'
            } else {
                $startTime = Carbon::parse($startTimeRaw)->format('H:i'); // 任意フォーマットから 'H:i'
            }

            $startTimeCarbon = Carbon::createFromFormat('H:i', $startTime); // 正常に変換
            $endTime = $startTimeCarbon->copy()->addHours(rand(8, 10))->addMinutes(rand(0, 59)); // 8~10時間の勤務を設定
 
            $attendance->update([
                 'end_time' => $endTime->format('H:i'),
            ]);
        });
    }
}
