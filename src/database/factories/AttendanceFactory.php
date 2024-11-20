<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;

class AttendanceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $workDate = $this->faker->dateTimeBetween('-1 month', 'now')->format('Y-m-d');
        $startTime = Carbon::createFromFormat('H:i', $this->faker->time('H:i'));
        
            
        return [
            'user_id' => User::factory(), // ユーザーIDを関連付け
            'work_date' => $workDate,
            'start_time' => $startTime->format('H:i'),
            'end_time' =>  null,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
