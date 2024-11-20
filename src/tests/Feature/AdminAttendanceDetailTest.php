<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Models\Attendance;
use App\Models\User;
use App\Models\Break_time;
use App\Models\Work_request;
use App\Models\Admin;
use Tests\TestCase;
use Carbon\Carbon;

class AdminAttendanceDetailTest extends TestCase
{
    use DatabaseTransactions;
    /**
     * A basic feature test example.
     *
     * @return void
     */

         // 勤怠詳細画面に表示されるデータが選択したものになっているか確認
    /** @test */
    public function attendance_detail_shows_correct_data()
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create(['user_id' => $user->id]);
        $workRequest = Work_request::create([
            'attendance_id' => $attendance->id,
            'user_id' => $user->id,
            'work_date' => '2024-11-20',
        ]);
        Break_time::create([
            'attendance_id' => $attendance->id,
            'break_start_time' => '12:00',
            'break_end_time' => '12:30',
        ]);
        // 管理者ユーザーを作成
        $admin = Admin::factory()->create();
        // 管理者としてログイン
        $this->actingAs($admin, 'admin');

        // 出勤時間や退勤時間が表示されているか
        $response = $this->get(route('admin.show', ['id' => $attendance->id]));
        $response->assertStatus(200)
                 ->assertSee(Carbon::parse($attendance->start_time)->format('H:i'))
                 ->assertSee(Carbon::parse($attendance->end_time)->format('H:i'))
                 ->assertSee(Carbon::parse($attendance->work_date)->format('Y'))
                 ->assertSee(Carbon::parse($attendance->work_date)->format('m-d'));
    }

           // 出勤時間が退勤時間より後になっている場合、エラーメッセージが表示される
     /** @test */
     public function start_time_is_before_end_time()
     {
        $attendance = Attendance::factory()->create();
        $data = [
             'start_time' => '17:00',
             'end_time' => '09:00',
        ];
        // 管理者ユーザーを作成
        $admin = Admin::factory()->create();
        // 管理者としてログイン
        $this->actingAs($admin, 'admin');
 
        $response = $this->post(route('admin.attendance.update', $attendance->id), $data);
 
        $response->assertSessionHasErrors(['start_time']);
     }
 
            // 休憩開始時間が退勤時間より後になっている場合、エラーメッセージが表示される
     /** @test */
     public function break_start_time_is_before_end_time()
     {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create();
        $break_time = Break_time::create(['attendance_id' => $attendance->id]);
      
        // 管理者ユーザーを作成
        $admin = Admin::factory()->create();
          // 管理者としてログイン
        $this->actingAs($admin, 'admin');
        $data = [
            'start_time' => '09:00',
            'end_time' => '18:00',
            'break_start_time' => ['19:00'], // 勤務時間外
            'break_end_time' => ['20:00'], // 勤務時間外
            'reason' => '不正な休憩時間',
        ];
        $response = $this->post(route('admin.attendance.update', $attendance->id), $data);
        $response->assertSessionHasErrors(['break_start_time.0']);
     }

           // 休憩終了時間が退勤時間より後になっている場合、エラーメッセージが表示される
    /** @test */
    public function break_end_time_is_before_end_time()
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create();
        $break_time = Break_time::create(['attendance_id' => $attendance->id]);

         // 管理者ユーザーを作成
        $admin = Admin::factory()->create();
        // 管理者としてログイン
        $this->actingAs($admin, 'admin');
        $data = [
            'start_time' => '09:00',
            'end_time' => '16:00', // 退勤時間が休憩終了時間より前
            'break_start_time' => ['15:00'],
            'break_end_time' => ['17:00'],
            'reason' => '不正な休憩時間',
        ];

        $response = $this->post(route('admin.attendance.update', $attendance->id), $data);
        $response->assertSessionHasErrors(['break_end_time.0']);
    }

             // 備考欄が未入力の場合のエラーメッセージが表示される
    /** @test */
    public function test_reason_is_required()
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create();

        // 管理者ユーザーを作成
        $admin = Admin::factory()->create();
        // 管理者としてログイン
        $this->actingAs($admin, 'admin');
        $data = [
            'start_time' => '09:00',
            'end_time' => '17:00',
            'reason' => '', // 備考欄が未入力
        ];

        $response = $this->post(route('admin.attendance.update', $attendance->id), $data);
        $response->assertSessionHasErrors(['reason']);
    }
}
