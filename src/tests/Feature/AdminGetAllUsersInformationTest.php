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

class AdminGetAllUsersInformationTest extends TestCase
{
    use DatabaseTransactions;

        /*スタッフ一覧ページ、スタッフ別勤怠一覧ページの機能をテスト*/
    /**
     * A basic feature test example.
     *
     * @return void
     */
         /* 管理者ユーザーが全一般ユーザーの「氏名」「メールアドレス」を確認できる*/
    /** @test */
    public function it_displays_all_users_name_and_email_for_admin()
    {
        // 一般ユーザーを作成
        $user = User::factory()->create();

        // 管理者ユーザーを作成
        $admin = Admin::factory()->create();
    
        // 管理者としてログイン
        $this->actingAs($admin, 'admin');

        // ユーザー一覧ページにアクセス
        $response = $this->get('/admin/staff/list'); // ユーザー一覧ページのURLに変更
 
        // 一般ユーザーの氏名とメールアドレスが表示されていることを確認
        $response->assertStatus(200)
                 ->assertSee($user->name)
                 ->assertSee($user->email);
    }

         /*ユーザーの勤怠情報が正しく表示される*/
    /** @test */
    public function it_displays_user_attendance_for_admin()
    {
        // 一般ユーザーを作成
        $user = User::factory()->create();

        // 勤怠情報を作成
        $attendance = Attendance::factory()->create([
          'user_id' => $user->id,
          'work_date' => Carbon::today()->toDateString(),
        ]);

        // 管理者ユーザーを作成
        $admin = Admin::factory()->create();
        // 管理者としてログイン
        $this->actingAs($admin, 'admin');

        // ユーザー勤怠情報一覧ページにアクセス
        $response = $this->get(route('admin.staffdetail',['id' => $user->id]) ); // 勤怠一覧ページのURLに変更

        // 勤怠情報が表示されていることを確認
        $response->assertStatus(200)
                 ->assertSee($attendance->work_date)
                 ->assertSee($user->name);
    }

            /*「前月」を押下した時に表示月の前月の情報が表示される*/
    /** @test */
    public function it_displays_before_month_button_is_pressed()
    {
        // 一般ユーザーを作成
        $user = User::factory()->create();
        $previousMonth = now()->subMonth()->format('Y-m');
    
        // 前月の勤怠情報を作成
        $attendances = Attendance::factory()->count(3)->create([
          'user_id' => $user->id,
          'work_date' => now()->subMonth()->format('Y-m-d'),
        ]);

        // 管理者ユーザーを作成
        $admin = Admin::factory()->create();
        // 管理者としてログイン
        $this->actingAs($admin, 'admin');

        // 勤怠一覧ページにアクセス
        $response = $this->get('/admin/attendance/staff/' . $user->id . '?date=' . $previousMonth); // 適切なURLに変更

        // 前月ボタンが表示され、前月の勤怠情報が表示されていることを確認
        $response->assertStatus(200)
                 ->assertSee('←前月') // 前月ボタンが表示されているか確認
                 ->assertSeeInOrder($attendances->pluck('work_date')->toArray());
    }

             /*「翌月」を押下した時に表示月の翌月の情報が表示される*/
    /** @test */
    public function it_displays_next_month_button_is_pressed()
    {
        // 一般ユーザーを作成
        $user = User::factory()->create();
        $nextMonth = now()->addMonth()->format('Y-m');
    
        // 前月の勤怠情報を作成
        $attendances = Attendance::factory()->count(3)->create([
          'user_id' => $user->id,
          'work_date' => now()->addMonth()->format('Y-m-d'),
        ]);
        

        // 管理者ユーザーを作成
        $admin = Admin::factory()->create();
        // 管理者としてログイン
        $this->actingAs($admin, 'admin');

        // 勤怠一覧ページにアクセス
        $response = $this->get('/admin/attendance/staff/' . $user->id . '?date=' . $nextMonth); // 適切なURLに変更

        // 前月ボタンが表示され、前月の勤怠情報が表示されていることを確認
        $response->assertStatus(200)
                 ->assertSee('翌月→') // 前月ボタンが表示されているか確認
                 ->assertSeeInOrder($attendances->pluck('work_date')->toArray());
    }

             /*「詳細」を押下すると、その日の勤怠詳細画面に遷移する*/
    /** @test */
    public function it_redirects_to_attendance_detail_page_when_details_button_is_pressed()
    {
        // 一般ユーザーを作成
        $user = User::factory()->create();

        // 勤怠情報を作成
        $attendance = Attendance::factory()->create([
          'user_id' => $user->id,
          'work_date' => Carbon::today()->toDateString(),
        ]);

        // 管理者ユーザーを作成
        $admin = Admin::factory()->create();
        // 管理者としてログイン
        $this->actingAs($admin, 'admin');

        // 勤怠詳細ページに遷移するために詳細リンクをクリック
        $response = $this->get('/admin/attendance/staff/' . $user->id)
                         ->assertStatus(200)
                         ->assertSee(route('admin.show', ['id' => $attendance->id])); 
    }
}
