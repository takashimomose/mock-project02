<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class BreakTest extends TestCase
{
    use DatabaseTransactions;

    public function test_break_button()
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

        $response->assertSee('出勤');

        // 勤務中のレコードを作成するリクエストを送信
        $response = $this->post('/attendance', [
            'start_work' => Attendance::STATUS_WORKING,
        ]);

        $response = $this->get('/attendance');
        $response->assertSee('休憩入');

        // 休憩中のレコードを作成するリクエストを送信
        $response = $this->post('/attendance', [
            'start_break' => Attendance::STATUS_BREAK,
        ]);

        $response = $this->get('/attendance');
        $response->assertSee('休憩中');
    }

    public function test_break_any_number_of_times()
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

        $response->assertSee('出勤');

        // 勤務中のレコードを作成するリクエストを送信
        $response = $this->post('/attendance', [
            'start_work' => Attendance::STATUS_WORKING,
        ]);

        $response = $this->get('/attendance');
        $response->assertSee('休憩入');

        // 休憩中のレコードを作成するリクエストを送信
        $response = $this->post('/attendance', [
            'start_break' => Attendance::STATUS_BREAK,
        ]);

        $response = $this->get('/attendance');
        $response->assertSee('休憩戻');

        // 休憩終了にレコードを更新するリクエストを送信
        $response = $this->post('/attendance', [
            'end_break' => Attendance::STATUS_WORKING,
        ]);

        $response = $this->get('/attendance');
        $response->assertSee('休憩入');
    }

    public function test_back_to_work_button()
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

        $response->assertSee('出勤');

        // 勤務中のレコードを作成するリクエストを送信
        $response = $this->post('/attendance', [
            'start_work' => Attendance::STATUS_WORKING,
        ]);

        $response = $this->get('/attendance');
        $response->assertSee('休憩入');

        // 休憩中のレコードを作成するリクエストを送信
        $response = $this->post('/attendance', [
            'start_break' => Attendance::STATUS_BREAK,
        ]);

        $response = $this->get('/attendance');
        $response->assertSee('休憩戻');

        // 休憩終了にレコードを更新するリクエストを送信
        $response = $this->post('/attendance', [
            'end_break' => Attendance::STATUS_WORKING,
        ]);

        $response = $this->get('/attendance');
        $response->assertSee('休憩入');
        $response->assertSee('出勤中');
    }

    public function test_break_any_number_of_times_2()
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

        $response->assertSee('出勤');

        // 勤務中のレコードを作成するリクエストを送信
        $response = $this->post('/attendance', [
            'start_work' => Attendance::STATUS_WORKING,
        ]);

        $response = $this->get('/attendance');
        $response->assertSee('休憩入');

        // 休憩中のレコードを作成するリクエストを送信
        $response = $this->post('/attendance', [
            'start_break' => Attendance::STATUS_BREAK,
        ]);

        $response = $this->get('/attendance');
        $response->assertSee('休憩戻');

        // 休憩終了にレコードを更新するリクエストを送信
        $response = $this->post('/attendance', [
            'end_break' => Attendance::STATUS_WORKING,
        ]);

        $response = $this->get('/attendance');
        $response->assertSee('休憩入');

        // 休憩中のレコードを作成するリクエストを送信
        $response = $this->post('/attendance', [
            'start_break' => Attendance::STATUS_BREAK,
        ]);

        $response = $this->get('/attendance');
        $response->assertSee('休憩戻');
    }

    public function test_break_on_admin_page()
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

        $response->assertSee('出勤');

        // 勤務中のレコードを作成するリクエストを送信
        $response = $this->post('/attendance', [
            'start_work' => Attendance::STATUS_WORKING,
        ]);

        $response = $this->get('/attendance');
        $response->assertSee('休憩入');

        // 休憩中のレコードを作成するリクエストを送信
        $response = $this->post('/attendance', [
            'start_break' => Attendance::STATUS_BREAK,
        ]);

        $response = $this->get('/attendance');
        $response->assertSee('休憩戻');

        // 時間経過をシミュレート（例: 30分後）
        Carbon::setTestNow(now()->addMinutes(30));

        // 休憩終了にレコードを更新するリクエストを送信
        $response = $this->post('/attendance', [
            'end_break' => Attendance::STATUS_WORKING,
        ]);

        // ログアウト
        $this->post('/logout');

        // 管理者ユーザーを作成
        $adminUser = User::create([
            'role_id' => User::ROLE_ADMIN,
            'name' => '管理者ユーザー',
            'email' => 'admin@example.com',
            'password' => Hash::make('adminpassword123'),
        ]);

        // 管理者ログインページにアクセス
        $response = $this->get('/admin/login');
        $response->assertStatus(200);

        // 管理者ログインを試行
        $response = $this->post('/admin/login', [
            'email' => 'admin@example.com',
            'password' => 'adminpassword123',
        ]);

        // 認証されていることを確認
        $this->assertAuthenticatedAs($adminUser);

        // 管理者の勤怠リストページにアクセス
        $response = $this->get('/admin/attendance/list');
        $response->assertStatus(200);

        // 計算結果をアサーション
        $expectedBreakTime = 30; // 期待する休憩時間
        $response->assertSee((string) $expectedBreakTime);
    }
}
