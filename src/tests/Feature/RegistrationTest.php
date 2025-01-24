<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * @return void
     */
    public function test_name_is_required_validation_message()
    {
        // 会員登録ページを開く
        $response = $this->get('/register');
        $response->assertStatus(200);

        // 名前を入力せずに他の必要項目を入力
        $response = $this->post('/register', [
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        // バリデーションメッセージを確認
        $response->assertSessionHasErrors(['name' => 'お名前を入力してください']);
    }

    public function test_email_is_required()
    {
        // 必要なデータを準備して、メールアドレスを空にする
        $response = $this->post('/register', [
            'name' => 'テストユーザー',
            'email' => '',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        // メールアドレスが未入力であることによるエラーメッセージが表示されるか確認
        $response->assertSessionHasErrors(['email' => 'メールアドレスを入力してください']);
    }

    public function test_password_must_be_at_least_8_characters()
    {
        $response = $this->post('/register', [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => 'pass123', // 7文字のパスワード
            'password_confirmation' => 'pass123',
        ]);

        // パスワードが8文字未満であることによるエラーメッセージが表示されるか確認
        $response->assertSessionHasErrors(['password' => 'パスワードは8文字以上で入力してください']);
    }

    public function test_password_confirmation_must_match()
    {
        $response = $this->post('/register', [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'differentpassword', // 異なる確認用パスワード
        ]);

        // パスワードと確認用パスワードが一致しない場合のエラーメッセージを確認
        $response->assertSessionHasErrors(['password' => 'パスワードと一致しません']);
    }

    public function test_password_is_required()
    {
        // 必要なデータを準備して、パスワードを空にする
        $response = $this->post('/register', [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => '',
            'password_confirmation' => '',
        ]);

        // パスワードが未入力であることによるエラーメッセージが表示されるか確認
        $response->assertSessionHasErrors(['password' => 'パスワードを入力してください']);
    }

    public function test_all_required_fields_are_filled_correctly()
    {
        $response = $this->post('/register', [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        // ユーザー情報が登録され、メール認証画面にリダイレクトされることを確認
        $response->assertRedirect('/email/verify');  // メール認証画面へのリダイレクトを確認

        $this->assertDatabaseHas('users', [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
        ]);
    }
}
