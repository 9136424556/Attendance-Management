<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Models\User;
use App\Models\Attendance;
use App\Models\Work_request;
use Tests\TestCase;
use Carbon\Carbon;

class CorrectRequestTest extends TestCase
{
    use DatabaseTransactions;

    protected $user;
    protected $attendance;
    /**
     * A basic feature test example.
     *
     * @return void
     */
         /*申請一覧・申請詳細ページのテスト*/
    public function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->attendance = Attendance::factory()->create(['user_id' => $this->user->id]);
    }

     // 1.「承認待ち」にログインユーザーが行った申請が全て表示される
    /** @test */
    public function pending_requests_are_displayed_correctly()
    {
        Work_request::factory()->create([
            'attendance_id' => $this->attendance->id,
            'user_id' => $this->user->id,
            'status' => '承認待ち',
        ]);

        $response = $this->actingAs($this->user)->get(route('stamp_correction_request.list.user'));

        $response->assertStatus(200);
        $response->assertViewHas('workRequests', function ($requests) {
            return $requests->where('status', '承認待ち')->count() === 1;
        });
    }

     // 2.「承認済み」に管理者が承認した修正申請が全て表示される
    /** @test */
    public function approved_requests_are_displayed_correctly()
    {
        // 承認済みの申請を作成
        Work_request::factory()->create([
            'attendance_id' => $this->attendance->id,
            'user_id' => $this->user->id,
            'status' => '承認済み',
        ]);

        $response = $this->actingAs($this->user)->get(route('stamp_correction_request.list.user'));

        // レスポンスのステータスを確認
        $response->assertStatus(200);
        // 'approvedRequests' ビュー変数に '承認済み' の申請が含まれているか確認
        $response->assertViewHas('approvedRequests', function ($requests) {
            return $requests->where('status', '承認済み')->count() === 1;
        });
    }

    // 3. 各申請の「詳細」を押下すると申請詳細画面に遷移する
    /** @test */
    public function clicking_request_details_redirects_to_detail_view()
    {
        $workRequest = Work_request::factory()->create([
            'attendance_id' => $this->attendance->id,
            'user_id' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('stamp_correction_request.list.user'))
            ->assertStatus(200)
            ->assertSee(route('requested.show', ['id' => $workRequest->id]));
    }
}
