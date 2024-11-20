<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Work_request;
use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;

class Work_requestFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'attendance_id' => Attendance::factory(),  // 既存のAttendanceレコードを関連付ける
            'user_id' => User::factory(),  // 既存のUserレコードを関連付ける
            'work_date' => Carbon::today()->toDateString(),
            'start_time' => $this->faker->time(),
            'end_time' => $this->faker->time(),
            'break_start_time' => null,
            'break_end_time' => null,
            'reason' => $this->faker->text(),
            'status' => $this->faker->randomElement(['承認待ち', '承認済み']),
            'requested_at' => Carbon::now(),
            'approval_at' => null,
            'is_submitted' => false,
        ];
    }
}
