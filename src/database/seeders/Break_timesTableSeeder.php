<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Faker\Factory as Faker;
use App\Models\Break_time;
use App\Models\Attendance;
use Carbon\Carbon;

class Break_timesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Faker インスタンスを生成
        $faker = Faker::create();
        // 登録済みの出勤データを取得
        $attendances = Attendance::all();

        foreach ($attendances as $attendance) {
            // 各出勤データに対してランダムな休憩データを作成
            $breakStartTime = Carbon::createFromFormat('H:i', $faker->time('H:i')); // Faker を使用
            $breakEndTime = $breakStartTime->copy()->addMinutes(rand(15, 30)); // 15~30分の休憩を設定

            Break_time::create([
                'attendance_id' => $attendance->id,
                'break_start_time' => $breakStartTime->format('H:i:s'),
                'break_end_time' => $breakEndTime->format('H:i:s'),
            ]);
        }
    }
}
