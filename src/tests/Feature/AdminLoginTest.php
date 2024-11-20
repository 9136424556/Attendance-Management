<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AdminLoginTest extends TestCase
{
    use DatabaseTransactions;  //テストの終了時にすべてのデータベース操作がロールバック,既存のデータはそのまま残る
    /**
     * A basic feature test example.
     *
     * @return void
     */
    /** @test  */
    public function admin_login_fails_if_email_is_invalid()
    {
        //メールアドレスが未入力の場合
        $response = $this->post('/admin/login', [
           'password' => 'adminpassword123',
        ]);

        $response->assertSessionHasErrors(['email']);
    }

    /** @test  */
    public function admin_login_fails_if_password_is_invalid()
    {
        //パスワードが未入力の場合
        $response = $this->post('/admin/login', [
           'email' => 'admin@example.com',
        ]);

        $response->assertSessionHasErrors(['password']);
    }

         /*登録内容と一致しない場合、バリデーションメッセージが表示される*/
    /** @test  */
    public function adminlogin_fails_with_invalid_credentials()
    {
       // テスト用の管理者ユーザーを作成
       $admin = \App\Models\Admin::factory()->create([
           'email' => 'admin@example.com',
           'password' => bcrypt('adminpassword123'), // 正しいパスワード
       ]);

       // 間違ったメールアドレスでログインを試みる
       $response = $this->post('/login', [
           'email' => 'wrong@example.com', // 存在しないメールアドレス
           'password' => 'adminpassword123',  // 間違ったパスワード
       ]);

       // セッションにエラーメッセージが含まれているか確認
       $response->assertSessionHasErrors([
           'email' => 'ログイン情報が登録されていません', // エラーメッセージを確認
       ]);

       // ユーザーが認証されていないことを確認
       $this->assertGuest();
    }
}
