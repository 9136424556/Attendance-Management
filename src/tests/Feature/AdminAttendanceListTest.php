<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Models\User;
use App\Models\Admin;
use App\Models\Attendance;
use App\Models\Work_request;
use Tests\TestCase;
use Carbon\Carbon;

class AdminAttendanceListTest extends TestCase
{
    use DatabaseTransactions;

          /*管理者勤怠一覧ページの機能テスト*/
    /**
     * A basic feature test example.
     *
     * @return void
     */
         /*その日になされた全ユーザーの勤怠情報が正確に確認できる*/
    /** @test */
    public function it_displays_all_user_attendance_for_the_day()
    {
        // ユーザーを作成
      $user = User::factory()->create();

      // 出勤情報を作成
      $attendance = Attendance::factory()->create([
        'user_id' => $user->id,
        'work_date' => Carbon::today()->toDateString(),
      ]);

      // 管理者ユーザーを作成
      $admin = Admin::factory()->create();
      // 管理者としてログイン
      $this->actingAs($admin, 'admin');

      // 管理画面にアクセス
      $response = $this->get('/admin/attendance/list'); // 勤怠一覧ページのURLに変更

      // 管理者画面に勤怠情報が正しく表示されることを確認
      $response->assertStatus(200)
               ->assertSee($attendance->work_date)
               ->assertSee($user->name);
    }

             /*遷移した際に現在の日付が表示される*/
     /** @test */
    public function it_displays_current_date_on_attendance_list_page()
    {
      // 管理者ユーザーを作成
      $admin = Admin::factory()->create();
      // 管理者としてログイン
      $this->actingAs($admin, 'admin');

      // 管理画面にアクセス
      $response = $this->get('/admin/attendance/list') // 勤怠一覧ページのURLに変更
                       ->assertStatus(200)
                       ->assertSee(now()->format('Y-m-d')); // 今日の日付が表示されることを確認
    }

           /*「前日」を押下した時に前の日の勤怠情報が表示される*/
    /** @test */
    public function it_displays_previous_before_day_attendance()
    {
      $user = User::factory()->create();
      $previousDay = now()->subDay()->format('Y-m-d');
      $attendances = Attendance::factory()->count(3)->create([
        'user_id' => $user->id,
        'work_date' => $previousDay,
      ]);
        // 管理者ユーザーを作成
      $admin = Admin::factory()->create();
      // 管理者としてログイン
      $this->actingAs($admin, 'admin')
           ->get('/admin/attendance/list?date=' . $previousDay) // 勤怠一覧ページのURLに変更
           ->assertStatus(200)
           ->assertSee('←前日'); // 前日ボタンが表示されているか確認
    }

           /*「翌日」を押下した時に次の日の勤怠情報が表示される*/
    /** @test */
    public function it_displays_previous_next_day_attendance()
    {
      $user = User::factory()->create();
      $nextDay = now()->addDay()->format('Y-m-d');
      $attendances = Attendance::factory()->count(3)->create([
        'user_id' => $user->id,
        'work_date' => $nextDay,
      ]);
        // 管理者ユーザーを作成
      $admin = Admin::factory()->create();
      // 管理者としてログイン
      $this->actingAs($admin, 'admin')
           ->get('/admin/attendance/list?date=' . $nextDay) // 勤怠一覧ページのURLに変更
           ->assertStatus(200)
           ->assertSee('翌日→'); // 前日ボタンが表示されているか確認
    }
}
