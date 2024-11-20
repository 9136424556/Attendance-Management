<?php

namespace Database\Factories;

use App\Models\Attendance;
use App\Models\Break_time;
use Illuminate\Database\Eloquent\Factories\Factory;

class Break_timeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'attendance_id' => Attendance::factory(), // 外部キーのリレーションを自動で生成
            'break_start_time' => $this->faker->time('H:i:s'), // ランダムな時間
            'break_end_time' => null,
        ];
    }
}
