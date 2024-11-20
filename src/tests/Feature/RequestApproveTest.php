<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Models\Admin;
use App\Models\Work_request;
use App\Models\User;
use App\Models\Attendance;
use Tests\TestCase;

class RequestApproveTest extends TestCase
{
    use DatabaseTransactions;

    protected $admin;
    protected $user;
    protected $attendance;
               /*管理者用の申請一覧ページ、申請詳細・承認ページの機能テスト*/
    /**
     * A basic feature test example.
     *
     * @return void
     */

      // テストデータのセットアップ
    public function setUp(): void
    {
        parent::setUp();

        // 管理者と一般ユーザーを作成
        $this->admin = Admin::factory()->create();
        $this->user = User::factory()->create();

        // 勤怠データを一般ユーザー用に作成
        $this->attendance = Attendance::factory()->create([
            'user_id' => $this->user->id,
        ]);
    }
           /*承認待ちの修正申請が全て表示されている*/
    /** @test */
    public function pending_requests_are_displayed_correctly_for_admin()
    {
        $workRequest1 = Work_request::factory()->create([
            'attendance_id' => $this->attendance->id,
            'user_id' => $this->user->id,
            'status' => '承認待ち',
        ]);

        $workRequest2 = Work_request::factory()->create([
            'attendance_id' => $this->attendance->id,
            'user_id' => $this->user->id,
            'status' => '承認待ち',
        ]);

        $response = $this->actingAs($this->admin, 'admin')
                         ->get(route('stamp_correction_request.list.admin'));

        $response->assertStatus(200);
        $response->assertSee($workRequest1->id);
        $response->assertSee($workRequest2->id);
    }
          
            /*承認済みの修正申請が全て表示されている*/
     /** @test */
     public function approved_requests_are_displayed_correctly_for_admin()
     {
        $approvedRequest = Work_request::factory()->create([
            'attendance_id' => $this->attendance->id,
            'user_id' => $this->user->id,
            'status' => '承認済み',
        ]);

        $response = $this->actingAs($this->admin, 'admin')
                         ->get(route('stamp_correction_request.list.admin'));

        $response->assertStatus(200);
        $response->assertSee($approvedRequest->id);
     }

             /*修正申請の詳細ページへ遷移*/
     /** @test */
    public function clicking_request_details_redirects_to_admin_detail_view()
    {
        // 管理者ユーザーを作成
        $admin = Admin::factory()->create();

        // ユーザーとその勤怠情報を作成
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create(['user_id' => $user->id]);

        // 修正申請を作成
        $workRequest = Work_request::factory()->create([
            'attendance_id' => $attendance->id,
            'user_id' => $user->id,
        ]);

        // 管理者としてログイン
        $response = $this->actingAs($admin, 'admin')
                         ->get(route('stamp_correction_request.list.admin')); // 管理者用の申請一覧ページ

        // 申請詳細ページへのリンクが含まれていることを確認
        $response->assertSee(route('approve', ['attendance_correct_request' => $workRequest->id]));
    }

           /*修正申請の承認処理が正しく行われるかテスト*/
    /** @test */
    public function approval_processing_works_correctly_for_admin()
    {
        // 管理者ユーザーを作成
        $admin = Admin::factory()->create();

        // ユーザーとその勤怠情報を作成
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create(['user_id' => $user->id]);

        // 承認待ちの申請を作成
        $workRequest = Work_request::factory()->create([
            'attendance_id' => $attendance->id,
            'user_id' => $user->id,
            'status' => '承認待ち',
        ]);

        // 管理者としてログインして、承認処理を実行
        $response = $this->actingAs($admin, 'admin')
                         ->post(route('approve.request', ['attendance_correct_request' => $workRequest->id])); // 承認のためのルート

        // 承認後、申請のステータスが「承認済み」に変更されていることを確認
        $workRequest->refresh();
        $this->assertEquals('承認済み', $workRequest->status);
    }

}
