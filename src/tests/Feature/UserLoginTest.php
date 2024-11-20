<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class UserLoginTest extends TestCase
{
    use DatabaseTransactions;  //テストの終了時にすべてのデータベース操作がロールバック,既存のデータはそのまま残る
    /**
     * A basic feature test example.
     *
     * @return void
     */
   /** @test  */
   public function user_login_fails_if_email_is_invalid()
   {
       //メールアドレスが未入力の場合
       $response = $this->post('/login', [
           'password' => 'password123',
       ]);

       $response->assertSessionHasErrors(['email']);
   }

    /** @test  */
   public function user_login_fails_if_password_is_invalid()
   {
       //パスワードが未入力の場合
       $response = $this->post('/login', [
           'email' => 'test@example.com',
       ]);

       $response->assertSessionHasErrors(['password']);
   }

   /** @test */
   public function login_fails_with_invalid_credentials()
   {
       // テスト用のユーザーを作成
       $user = \App\Models\User::factory()->create([
           'email' => 'test@example.com',
           'password' => bcrypt('password123'), // 正しいパスワード
       ]);

       // 間違ったメールアドレスとパスワードでログインを試みる
       $response = $this->post('/login', [
           'email' => 'wrong@example.com', // 存在しないメールアドレス
           'password' => 'wrongpassword',  // 間違ったパスワード
       ]);

       // セッションにエラーメッセージが含まれているか確認
       $response->assertSessionHasErrors([
           'email' => 'ログイン情報が登録されていません', // エラーメッセージを確認
       ]);

       // ユーザーが認証されていないことを確認
       $this->assertGuest();
   }
}
