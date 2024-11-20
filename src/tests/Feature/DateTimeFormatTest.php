<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Models\User;
use Carbon\Carbon;

class DateTimeFormatTest extends TestCase
{
    use DatabaseTransactions;
    /**
     * A basic feature test example.
     *
     * @return void
     */
     /** @test */
     public function it_displays_datetime_in_correct_format()
     {
         // テスト用の認証済みユーザーを作成
         $user = User::factory()->create();

         // ログイン状態を設定
         $this->actingAs($user);

         // 対象のエンドポイントにリクエストを送る
         $response = $this->get('/attendance'); // 勤怠打刻画面のエンドポイント
 
         // 期待する日付フォーマット
         $expectedDate = \Carbon\Carbon::now()->format('Y-m-d');
 
         // レスポンス内に期待する日時フォーマットが含まれることを確認
         $response->assertSee($expectedDate);
     }
}
