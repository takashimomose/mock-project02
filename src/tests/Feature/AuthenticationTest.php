<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use DatabaseTransactions;

    public function test_email_is_required_for_login()
    {
        // ログインページへのアクセス
        $response = $this->get('/login');
        $response->assertStatus(200);

        // メールアドレスを空にして他の必要項目を入力
        $response = $this->post('/login', [
            'email' => '', // メールアドレスを未入力
            'password' => 'password123',
        ]);

        // バリデーションエラーメッセージが表示されることを確認
        $response->assertSessionHasErrors(['email' => 'メールアドレスを入力してください']);
    }

    public function test_password_is_required_for_login()
    {
        // ログインページへのアクセス
        $response = $this->get('/login');
        $response->assertStatus(200);

        // パスワードを空にして他の必要項目を入力
        $response = $this->post('/login', [
            'email' => 'test@example.com', // メールアドレスを入力
            'password' => '', // パスワードを未入力
        ]);

        // バリデーションエラーメッセージが表示されることを確認
        $response->assertSessionHasErrors(['password' => 'パスワードを入力してください']);
    }

    public function test_unregistered_user_login_fails_with_error_message()
    {
        // ログインページへのアクセス
        $response = $this->get('/login');
        $response->assertStatus(200);

        // 未登録のユーザー情報を入力してログインを試みる
        $response = $this->post('/login', [
            'email' => 'unregistered@example.com', // 未登録のメールアドレス
            'password' => 'password123', // パスワード
        ]);

        // 「ログイン情報が登録されていません」というエラーメッセージが表示されることを確認
        $response->assertSessionHasErrors(['email' => 'ログイン情報が登録されていません']);
    }
}
