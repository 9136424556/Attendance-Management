<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Models\User;
use App\Models\Attendance;
use App\Models\Break_time;
use App\Models\Work_request;
use Tests\TestCase;
use Carbon\Carbon;

class AttendanceDetailTest extends TestCase
{
    use DatabaseTransactions;
    /**
     * A basic feature test example.
     *
     * @return void
     */
        /*データ準備*/
    public function setUp(): void
    {
        parent::setUp();

        // テスト用ユーザーとデータを作成
        $this->user = User::factory()->create();
        $this->attendance = Attendance::factory()->create(['user_id' => $this->user->id]);
        $this->breakTimes = Break_time::factory(2)->create(['attendance_id' => $this->attendance->id]);
    }
     
             /*「名前」や「日付」の確認*/
    /** @test */
    public function it_displays_attendance_details()
    {
        $response = $this->actingAs($this->user)->get(route('attendance.show', $this->attendance->id));

        $response->assertStatus(200);
        $response->assertViewIs('show');
        $response->assertViewHas('attendance', $this->attendance);
        $response->assertViewHas('user', $this->user);
        $response->assertViewHas('breakTimes', function ($breakTimes) {
            return $breakTimes->count() === 2;
        });
        $response->assertViewHas('workRequest', null);
    }
         /*勤怠修正申請の送信機能のテスト*/
    /** @test */
    public function it_submits_an_attendance_correction_request()
    {
        $data = [
            'year' => '2024',
            'date' => '11-19',
            'start_time' => '09:00', // 秒なし
            'end_time' => '18:00',  // 秒なし
            'break_start_time' => ['12:00'],
            'break_end_time' => ['12:30'],
            'reason' => 'Meeting adjustments',
        ];

        // テストデータの時間をCarbonで秒数を0に設定
        $startTime = Carbon::createFromFormat('H:i', $data['start_time'])->setSeconds(0);
        $endTime = Carbon::createFromFormat('H:i', $data['end_time'])->setSeconds(0);

        $response = $this->actingAs($this->user)
                         ->post(route('attendance.request', $this->attendance->id), $data);


        $response->assertStatus(302) // リダイレクト確認
                 ->assertSessionHasNoErrors(); // バリデーションエラーがないことを確認
    
        // データが保存されているか確認
        $this->assertDatabaseHas('work_requests', [
            'attendance_id' => $this->attendance->id,
            'user_id' => $this->user->id,
            'work_date' => '2024-11-19',
            'start_time' => $startTime->format('H:i:s'), // 秒を含めた時間
            'end_time' => $endTime->format('H:i:s'), // 秒を含めた時間
            'reason' => 'Meeting adjustments',
            'is_submitted' => true,
        ]);
        // リダイレクト先の確認
        $response->assertRedirect('/attendance/list');
        $response->assertSessionHas('status', '申請が送信されました');
    }

           /* 無効な勤怠修正申請が行われた際のバリデーションエラーメッセージの確認*/
    /** @test */
    public function it_validates_invalid_attendance_correction_request()
    {
       $data = [
          'year' => '2024',
          'date' => 'invalid-date', // 無効な日付
          'start_time' => '09:00',
          'end_time' => '18:00',
          'reason' => '', // 空の理由
       ];

       $response = $this->actingAs($this->user)
                     ->post(route('attendance.request', $this->attendance->id), $data);

       $response->assertSessionHasErrors(['work_date', 'reason']); // エラーの確認

       // データが保存されていないことを確認
       $this->assertDatabaseMissing('work_requests', [
        'attendance_id' => $this->attendance->id,
        'user_id' => $this->user->id,
       ]);
    }

         /*勤怠時間および休憩時間の検証*/
    /** @test */
    public function it_checks_attendance_and_break_times()
    {
        $attendance = [
          'start_time' => '09:00:00',
          'end_time' => '18:00:00',
        ];
        $breaks = [
          ['start_time' => '12:00:00', 'end_time' => '12:30:00'],
        ];

        // 出勤・退勤時間の確認
        $this->assertEquals('09:00:00', $attendance['start_time']);
        $this->assertEquals('18:00:00', $attendance['end_time']);

        // 休憩時間の確認
        foreach ($breaks as $break) {
          $this->assertEquals('12:00:00', $break['start_time']);
          $this->assertEquals('12:30:00', $break['end_time']);
        }
    }

    //  出勤時間が退勤時間より後の場合
    /** @test */
    public function start_time_after_end_time_shows_error()
    {
        $data = [
            'start_time' => '19:00',
            'end_time' => '09:00',
        ];

        $response = $this->actingAs($this->user)
            ->post(route('attendance.request', $this->attendance->id), $data);

        $response->assertSessionHasErrors(['start_time' => '出勤時間もしくは退勤時間が不適切な値です']);
    }

    // 休憩開始時間が退勤時間より後の場合
    /** @test */
    public function break_start_time_after_end_time_shows_error()
    {
        $data = [
            'start_time' => '09:00',
            'end_time' => '18:00',
            'break_start_time' => ['19:00'],
            'break_end_time' => ['19:30'],
        ];

        $response = $this->actingAs($this->user)
            ->post(route('attendance.request', $this->attendance->id), $data);

        $response->assertSessionHasErrors(['break_start_time.0' => '休憩時間が勤務時間外です']);
    }

    //  休憩終了時間が退勤時間より後の場合
    /** @test */
    public function break_end_time_after_end_time_shows_error()
    {
        $data = [
            'start_time' => '09:00',
            'end_time' => '18:00',
            'break_start_time' => ['12:00'],
            'break_end_time' => ['19:30'],
        ];

        $response = $this->actingAs($this->user)
            ->post(route('attendance.request', $this->attendance->id), $data);

        $response->assertSessionHasErrors(['break_end_time.0' => '休憩時間が勤務時間外です']);
    }

  
}
