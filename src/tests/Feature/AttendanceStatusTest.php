<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Attendance;
use App\Models\Break_time;
use App\Models\Admin;
use App\Models\Work_request;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Carbon\Carbon;

class AttendanceStatusTest extends TestCase
{
    use DatabaseTransactions; 
    /**
     * A basic feature test example.
     *
     * @return void
     */
         /*勤務外のとき、勤怠ステータスが正しく表示されるか*/
    /** @test */
    public function it_displays_default_status_as_before_clock_in()
    {
        // 1. 初期状態のユーザーを作成
        $user = User::factory()->create();

        // 2. ユーザーとしてログイン
        $this->actingAs($user);

        // 3. 勤怠打刻画面を開く
        $response = $this->get('/attendance');

        // 4. 初期ステータスを確認
        $response->assertViewHas('attendanceStatus', 'beforeClockIn');
        $response->assertViewHas('workStatus', '勤務外');
    }
          /*出勤中のとき、勤怠ステータスが正しく表示されるか*/
     /** @test */
    public function it_changes_status_to_start_attendance_clock_in_button()
    {
         // 1. 初期状態のユーザーを作成
         $user = User::factory()->create();
 
         // 2. ユーザーとしてログイン
         $this->actingAs($user);
 
         // 3. 出勤ボタンを押す
         $this->post('/attendance/start'); // 出勤ボタンのエンドポイントを実行
 
         // 4. 勤怠打刻画面を開く
         $response = $this->get('/attendance');
 
         // 5. ステータスを確認
         $response->assertViewHas('attendanceStatus', 'clockedIn');
         $response->assertViewHas('workStatus', '出勤中');
    }
 
          /*休憩中のとき、勤怠ステータスが正しく表示されるか*/
    /** @test */
    public function it_changes_status_to_on_break_time_button()
    {
        
        // 1. 出勤済みのユーザーと出勤記録を作成
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => Carbon::today(),
            'start_time' =>  '09:00',
            'end_time' => null,
        ]);
       
        // 2. ユーザーとしてログイン
        $this->actingAs($user);

        // 3. 休憩ボタンを押す
        $response = $this->post('/attendance/breakstart', ['attendance_id' => $attendance->id]) ->assertRedirect();; // 休憩ボタンのエンドポイント

          // 4. リダイレクト後のセッション値を確認
        $this->assertEquals('onBreak', session('status'));
        $this->assertEquals('休憩中', session('workStatus'));

        // 4. 勤怠打刻画面を開く
        $response = $this->get('/attendance');
        

        // 5. ステータスを確認
        $response->assertViewHas('attendanceStatus','onBreak');
        $response->assertViewHas('workStatus', '休憩中');
    }
           /*退勤済みのとき、勤怠ステータスが正しく表示されるか*/
    /** @test */
    public function it_changes_status_to_attendance_end_button()
    {
        // 1. 出勤済みのユーザーと出勤記録を作成
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => Carbon::today(),
        ]);

        // 2. ユーザーとしてログイン
        $this->actingAs($user);

        // 3. 退勤ボタンを押す
        $this->post('/attendance/end', ['attendance_id' => $attendance->id]); // 休憩ボタンのエンドポイント

        // 4. 勤怠打刻画面を開く
        $response = $this->get('/attendance');

        // 5. ステータスを確認
        $response->assertViewHas('attendanceStatus', 'afterend');
        $response->assertViewHas('workStatus', '退勤済み');
    }

    
        /* 出勤機能のテスト*/
    /** @test */
    public function user_can_start_attendance()
    {
        // テスト用のユーザーを作成
        $user = User::factory()->create();
        // 2. ユーザーとしてログイン
        $this->actingAs($user);
        // 出勤を開始
        $response = $this->post('/attendance/start');
         // レスポンスがリダイレクトであることを確認
        $response->assertRedirect();

         // データベースに出勤レコードが作成されていることを確認
        $this->assertDatabaseHas('attendances', [
             'user_id' => $user->id,
             'work_date' => Carbon::today()->toDateString(),
             'start_time' => Carbon::now()->toTimeString(),
        ]);
         // セッションの値を確認
        $response->assertSessionHas('status', 'clockedIn');
        $response->assertSessionHas('workStatus', '出勤中');
    }
            /*出勤ボタンが１日１回しか押せないかテスト*/
    /** @test */
    public function user_can_start_attendance_only_once_per_day()
    {
        // ユーザー作成
        $user = User::factory()->create();

        // 今日の日付を取得
        $today = Carbon::today();

        // 最初の出勤を記録
        $this->actingAs($user);
        $response1 = $this->post('/attendance/start');

        // セッションに出勤中のステータスが設定されていることを確認
        $response1->assertSessionHas('status', 'clockedIn');
        $response1->assertSessionHas('workStatus', '出勤中');

        // 出勤情報がDBに記録されていることを確認
        $this->assertDatabaseHas('attendances', [
          'user_id' => $user->id,
          'work_date' => $today,
        ]);

        // もう一度出勤を試みる
        $response2 = $this->post('/attendance/start');
        // 2回目のリクエストでもセッションは同じステータスのまま
        $response2->assertSessionHas('status', 'clockedIn');
        $response2->assertSessionHas('workStatus', '出勤中');

        // DBに新たな出勤情報が記録されていないことを確認
        $this->assertEquals(1, Attendance::where('user_id', $user->id)->where('work_date', $today)->count());
    }

            /*管理画面で出勤時刻が確認できることのテスト*/
    /** @test */
    public function starttimes_are_displayed_in_the_management_screen()
    {
        // ユーザーを作成
        $user = User::factory()->create();
        // 出勤情報を作成
        $attendance = Attendance::factory()->create([
          'user_id' => $user->id,
          'work_date' => Carbon::today()->toDateString(),
          'start_time' => Carbon::now()->toTimeString(),
        ]);
        // 勤怠変更リクエストを作成
        $workRequest = Work_request::factory()->create([
          'attendance_id' => $attendance->id,
          'start_time' => Carbon::now()->toTimeString(), 
        ]);

        // 管理者ユーザーを作成
        $admin = Admin::factory()->create();
        // 管理者としてログイン
        $this->actingAs($admin, 'admin');

        // 管理画面にアクセス
        $response = $this->get('/admin/attendance/' . $attendance->id); // 適切なURLに変更
      
        $response->assertViewHas('attendance', function ($attendance) use ($workRequest) {
           // $workRequest->start_time を Carbon インスタンスに変換
          return $attendance->start_time->equalTo(Carbon::parse($workRequest->start_time));  
        });
    }

         /* 退勤機能のテスト*/
     /** @test */
    public function user_can_end_attendance()
    {
        // テスト用のユーザーを作成
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => Carbon::today()->toDateString(),
        ]);
        $this->actingAs($user);
        // 退勤を記録
        $response = $this->post('/attendance/end');
        // レスポンスがリダイレクトであることを確認
        $response->assertRedirect();
        // 出勤レコードに退勤時刻が記録されていることを確認
        $this->assertDatabaseHas('attendances', [
            'id' => $attendance->id,
            'end_time' => Carbon::now()->toTimeString(),
        ]);

        // セッションの値を確認
        $response->assertSessionHas('status', 'afterend');
        $response->assertSessionHas('workStatus', '退勤済み');
    }

            /*管理画面で退勤時刻が確認できることのテスト*/
    /** @test */
    public function endtimes_are_displayed_in_the_management_screen()
    {
        // ユーザーを作成
        $user = User::factory()->create();
        // 出勤情報を作成
        $attendance = Attendance::factory()->create([
          'user_id' => $user->id,
          'work_date' => Carbon::today()->toDateString(),
          'start_time' => Carbon::now()->toTimeString(),
          'end_time' => Carbon::now()->addHours(8)->toTimeString(), // 退勤時刻を設定
        ]);
        // 勤怠変更リクエストを作成
        $workRequest = Work_request::factory()->create([
          'attendance_id' => $attendance->id,
          'start_time' => Carbon::now()->toTimeString(), 
          'end_time' => Carbon::now()->addHours(8)->toTimeString(),
        ]);

        // 管理者ユーザーを作成
        $admin = Admin::factory()->create();
        // 管理者としてログイン
        $this->actingAs($admin, 'admin');

        // 管理画面にアクセス
        $response = $this->get('/admin/attendance/' . $attendance->id); // 適切なURLに変更
      
        $response->assertViewHas('attendance', function ($attendance) use ($workRequest) {
           // $attendance->end_time と $workRequest->end_time を比較
          return $attendance->end_time->equalTo(Carbon::parse($workRequest->end_time));  
        });
    }

         /*休憩開始機能テスト*/
    /** @test */
    public function user_can_breaktime_start()
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => Carbon::today()->toDateString(),
        ]);
        $this->actingAs($user);

        // 休憩開始を記録
        $response = $this->post('/attendance/breakstart');
        // レスポンスがリダイレクトであることを確認
        $response->assertRedirect();
        // 休憩レコードが作成されていることを確認
        $this->assertDatabaseHas('break_times', [
            'attendance_id' => $attendance->id,
            'break_start_time' => Carbon::now()->toTimeString(),
        ]);

        // セッションの値を確認
        $this->assertEquals(session('status'), 'onBreak');
        $this->assertEquals(session('workStatus'), '休憩中');

    }
        /*休憩が一日に何回でもできるかのテスト*/
    /** @test */
    public function user_can_take_multiple_breaktimes_in_one_day()
    {
      $user = User::factory()->create();
      $attendance = Attendance::factory()->create([
        'user_id' => $user->id,
        'work_date' => Carbon::today()->toDateString(),
      ]);
      $this->actingAs($user);

      // 1回目の休憩開始
      $response1 = $this->post('/attendance/breakstart');
      $response1->assertRedirect();
      $this->assertDatabaseHas('break_times', [
        'attendance_id' => $attendance->id,
        'break_start_time' => Carbon::now()->toTimeString(),
      ]);

      // 2回目の休憩開始
      $response2 = $this->post('/attendance/breakstart');
      $response2->assertRedirect();
      $this->assertDatabaseHas('break_times', [
        'attendance_id' => $attendance->id,
        'break_start_time' => Carbon::now()->toTimeString(),
      ]);
    }    
         /*休憩終了機能テスト*/
    /** @test */
    public function user_can_end_breaktime()
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => Carbon::today()->toDateString(),
        ]);
        $breakTime = Break_time::factory()->create([
            'attendance_id' => $attendance->id,
            'break_start_time' => Carbon::now()->subMinutes(30)->toTimeString(),
        ]);
        $this->actingAs($user);
        // 休憩終了を記録
        $response = $this->post('/attendance/breakend');
        // レスポンスがリダイレクトであることを確認
        $response->assertRedirect();

        // 休憩レコードに終了時刻が記録されていることを確認
        $this->assertDatabaseHas('break_times', [
            'id' => $breakTime->id,
            'break_end_time' => Carbon::now()->toTimeString(),
        ]);
        
        // セッションの値を確認
        $response->assertSessionHas('status', 'clockedIn');
        $response->assertSessionHas('workStatus', '出勤中');

    }
            /*休憩戻りが一日に何回でもできるかのテスト*/
    /** @test */
    public function user_can_end_multiple_breaktimes_in_one_day()
    {
      $user = User::factory()->create();
      $attendance = Attendance::factory()->create([
        'user_id' => $user->id,
        'work_date' => Carbon::today()->toDateString(),
      ]);
      // 休憩時間を設定
      $breakTime1 = Break_time::factory()->create([
        'attendance_id' => $attendance->id,
        'break_start_time' => Carbon::now()->subMinutes(30)->toTimeString(),
      ]);
      $breakTime2 = Break_time::factory()->create([
        'attendance_id' => $attendance->id,
        'break_start_time' => Carbon::now()->subMinutes(60)->toTimeString(),
      ]);
      $this->actingAs($user);
      // 固定の時刻（ここでは現在の時刻に合わせる）
      $expectedEndTime = Carbon::now()->toTimeString();

      // 1回目の休憩終了
      $response1 = $this->post('/attendance/breakend');
      $response1->assertRedirect();
      $this->assertDatabaseHas('break_times', [
        'id' => $breakTime1->id,
        'break_end_time' => $expectedEndTime,
      ]);

      // 2回目の休憩終了
      $response2 = $this->post('/attendance/breakend');
      $response2->assertRedirect();
      $this->assertDatabaseHas('break_times', [
        'id' => $breakTime2->id,
        'break_end_time' => $expectedEndTime,
      ]);
    }
            /*管理画面で休憩時刻が確認できることのテスト*/
    /** @test */
    public function breaktimes_are_displayed_in_the_management_screen()
    {
      // ユーザーを作成
      $user = User::factory()->create();
      // 出勤情報を作成
      $attendance = Attendance::factory()->create([
        'user_id' => $user->id,
        'work_date' => Carbon::today()->toDateString(),
      ]);
       // 休憩情報を作成
      $breakTime = Break_time::factory()->create([
        'attendance_id' => $attendance->id,
        'break_start_time' => '09:00:00',
        'break_end_time' => '09:30:00',
      ]);

      // 管理者ユーザーを作成
      $admin = Admin::factory()->create();
      // 管理者としてログイン
      $this->actingAs($admin, 'admin');

      // 管理画面にアクセス
      $response = $this->get('/admin/attendance/' . $attendance->id); // 適切なURLに変更
      
      $response->assertViewHas('breakTimes', function ($breakTimes) use ($breakTime) {
        return $breakTimes->contains($breakTime);
      });
    }
}
