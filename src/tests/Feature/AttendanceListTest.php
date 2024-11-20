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

class AttendanceListTest extends TestCase
{
    use DatabaseTransactions;
    /**
     * A basic feature test example.
     *
     * @return void
     */
          /*自分が行った勤怠情報が全て表示されているかテスト*/
    /** @test */
    public function it_displays_all_attendance_records_for_logged_in_user()
    {
        $user = User::factory()->create();
        $attendances = Attendance::factory()->count(5)->create([
            'user_id' => $user->id,
            'work_date' => now()->startOfMonth()->addDays(rand(0, 30)), // 11月の日付をランダムに設定
        ]);
        // ここでwork_dateをフォーマットして配列を作成
        $formattedDates = $attendances->sortBy('work_date')->pluck('work_date')->map(function ($date) {
          return \Carbon\Carbon::parse($date)->format('Y-m-d');  // ビューと同じフォーマットに合わせる
        })->toArray();
         // テスト実行
        $response = $this->actingAs($user)->get('/attendance/list');

         // レスポンスのコンテンツを整形（必要に応じて改行などを削除）
        $responseContent = strip_tags($response->getContent()); // HTMLタグを取り除く
        preg_match_all('/\d{4}-\d{2}-\d{2}/', $responseContent, $matches); // 日付フォーマットを抽出

        $responseDates = array_values(array_filter($matches[0], function ($date) use ($formattedDates) {
            return in_array($date, $formattedDates); // 必要な日付のみ残す
        })); // 抽出した日付を配列として取得
        
        // フォーマットした日付とレスポンスから抽出した日付を比較
        $this->assertEquals($formattedDates, $responseDates);
    }

           /*勤怠一覧画面に遷移した際に現在の月が表示*/
    /** @test */
    public function it_displays_current_month_on_attendance_list_page()
    {
        $user = User::factory()->create();

        $this->actingAs($user)
         ->get('/attendance/list')
         ->assertStatus(200)
         ->assertSee(now()->format('Y-m'));
    }

            /* 前月ボタンの動作確認*/
    /** @test */
    public function it_displays_previous_before_month()
    {
        $user = User::factory()->create();
        $previousMonth = now()->subMonth()->format('Y-m');
        $attendances = Attendance::factory()->count(3)->create([
          'user_id' => $user->id,
          'work_date' => now()->subMonth()->format('Y-m-d'),
        ]);

        $this->actingAs($user)
         ->get('/attendance/list?date=' . $previousMonth)
         ->assertStatus(200)
         ->assertSee('←前月') // ボタンが表示されているか確認
         ->assertSeeInOrder($attendances->pluck('work_date')->toArray());
    }

            /* 翌月ボタンの動作確認*/
    /** @test */
    public function it_displays_previous_next_month()
    {
        $user = User::factory()->create();
        $nextMonth = now()->addMonth()->format('Y-m');
        $attendances = Attendance::factory()->count(3)->create([
          'user_id' => $user->id,
          'work_date' => now()->addMonth()->format('Y-m-d'),
        ]);

        $this->actingAs($user)
         ->get('/attendance/list?date=' . $nextMonth)
         ->assertStatus(200)
         ->assertSee('翌月→'); // ボタンが表示されているか確認
    }

             /*詳細リンクの動作確認*/
    /** @test */
    public function it_displays_correct_attendance_detail_link_or_work_requests()
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create([
          'user_id' => $user->id,
          'work_date' => now()->format('Y-m-d'),
        ]);

        // 修正リクエストがない場合
        $this->actingAs($user)
         ->get('/attendance/list')
         ->assertStatus(200)
         ->assertSee(route('attendance.show', ['id' => $attendance->id]));

        // 修正リクエストがある場合
        $workRequest = Work_request::factory()->create([
          'user_id' => $user->id,
          'attendance_id' => $attendance->id,
          'is_submitted' => true,
        ]);
        $this->actingAs($user)
        ->get('/attendance/list')
        ->assertStatus(200)
        ->assertSee(route('requested.show', ['id' => $workRequest->id]));
    }
}
