<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AttendanceStatusTest extends TestCase
{
    use DatabaseTransactions;

    public function test_status_before()
    {
        // ログインのユーザーを作成
        $user = User::create([
            'role_id' => User::ROLE_GENERAL,
            'name' => 'テストユーザー',
            'email' => 'registered@example.com',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
        ]);

        // ログインページへのアクセス
        $response = $this->get('/login');
        $response->assertStatus(200);

        // ログインを試行
        $response = $this->post('/login', [
            'email' => 'registered@example.com',
            'password' => 'password123',
        ]);

        // 認証されていることを確認
        $this->assertAuthenticatedAs($user);

        // 勤怠画面へのリダイレクトを確認
        $response->assertRedirect('/attendance');

        // /attendance にアクセス
        $response = $this->get('/attendance');
        $response->assertStatus(200);

        $response->assertSee('勤務外');
    }

    public function test_status_working()
    {
        // ログインのユーザーを作成
        $user = User::create([
            'role_id' => User::ROLE_GENERAL,
            'name' => 'テストユーザー',
            'email' => 'registered@example.com',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
        ]);

        // ログインページへのアクセス
        $response = $this->get('/login');
        $response->assertStatus(200);

        // ログインを試行
        $response = $this->post('/login', [
            'email' => 'registered@example.com',
            'password' => 'password123',
        ]);

        // 認証されていることを確認
        $this->assertAuthenticatedAs($user);

        // 勤務中のレコードを作成するリクエストを送信
        $response = $this->post('/attendance', [
            'start_work' => Attendance::STATUS_WORKING,
        ]);

        // 勤怠画面へのリダイレクトを確認
        $response->assertRedirect('/attendance');

        // /attendance にアクセス
        $response = $this->get('/attendance');
        $response->assertStatus(200);

        $response->assertSee('出勤中');
    }

    public function test_status_break()
    {
        // ログインのユーザーを作成
        $user = User::create([
            'role_id' => User::ROLE_GENERAL,
            'name' => 'テストユーザー',
            'email' => 'registered@example.com',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
        ]);

        // ログインページへのアクセス
        $response = $this->get('/login');
        $response->assertStatus(200);

        // ログインを試行
        $response = $this->post('/login', [
            'email' => 'registered@example.com',
            'password' => 'password123',
        ]);

        // 認証されていることを確認
        $this->assertAuthenticatedAs($user);

        // 勤務中のレコードを作成するリクエストを送信
        $response = $this->post('/attendance', [
            'start_work' => Attendance::STATUS_WORKING,
        ]);
        $response->assertRedirect('/attendance');

        // 休憩中のステータスを設定するリクエストを送信
        $response = $this->post('/attendance', [
            'start_break' => Attendance::STATUS_BREAK,
        ]);

        // 勤怠画面へのリダイレクトを確認
        $response->assertRedirect('/attendance');

        // /attendance にアクセス
        $response = $this->get('/attendance');
        $response->assertStatus(200);

        $response->assertSee('休憩中');
    }

    public function test_status_finished()
    {
        // ログインのユーザーを作成
        $user = User::create([
            'role_id' => User::ROLE_GENERAL,
            'name' => 'テストユーザー',
            'email' => 'registered@example.com',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
        ]);

        // ログインページへのアクセス
        $response = $this->get('/login');
        $response->assertStatus(200);

        // ログインを試行
        $response = $this->post('/login', [
            'email' => 'registered@example.com',
            'password' => 'password123',
        ]);

        // 認証されていることを確認
        $this->assertAuthenticatedAs($user);

        // 勤務開始のリクエストを送信
        $response = $this->post('/attendance', [
            'start_work' => Attendance::STATUS_WORKING,
        ]);
        $response->assertRedirect('/attendance');

        // 退勤済のリクエストを送信
        $response = $this->post('/attendance', [
            'end_work' => Attendance::STATUS_FINISHED,
        ]);

        // 勤怠画面へのリダイレクトを確認
        $response->assertRedirect('/attendance');

        // /attendance にアクセス
        $response = $this->get('/attendance');
        $response->assertStatus(200);

        $response->assertSee('退勤済');
    }
}
