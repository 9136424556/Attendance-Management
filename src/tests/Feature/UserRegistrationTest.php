<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class UserRegistrationTest extends TestCase
{
    use DatabaseTransactions;  //テストの終了時にすべてのデータベース操作がロールバック,既存のデータはそのまま残る
    /**
     * A basic feature test example.
     *
     * @return void
     */
    /** @test */
    public function user_registration_fails_if_name_is_missing()
    {
         //名前が未入力の場合
        $response = $this->post('/register', [
            'email' => 'user@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors(['name']);
    }

    /** @test  */
    public function user_registration_fails_if_email_is_invalid()
    {
        //メールアドレスが未入力の場合
        $response = $this->post('/register', [
            'name' => 'Test User',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors(['email']);
    }

     /** @test */
     public function registration_fails_if_password_is_less_than_8_characters()
     {
         // パスワードが8文字未満の場合
         $response = $this->post('/register', [
             'name' => 'Test User',
             'email' => 'test@example.com',
             'password' => '1234567',
             'password_confirmation' => '1234567',
         ]);
 
         $response->assertSessionHasErrors(['password']);
     }

     /** @test */
    public function registration_fails_if_password_confirmation_does_not_match()
    {
        // 確認用パスワードが一致しない場合
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'differentPassword',
        ]);

        $response->assertSessionHasErrors(['password']);
    }

    /** @test */
    public function registration_succeeds_with_valid_data()
    {
        // 正しいユーザー情報を送信
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect('/attendance'); // 登録後にリダイレクトされることを確認
    }
}
