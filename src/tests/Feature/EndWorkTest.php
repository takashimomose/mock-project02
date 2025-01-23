<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class EndWorkTest extends TestCase
{
    use DatabaseTransactions;

    private function createUser()
    {
        return User::create([
            'role_id' => User::ROLE_GENERAL,
            'name' => 'テストユーザー',
            'email' => 'registered@example.com',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
        ]);
    }

    public function test_finished_work_button()
    {
        // ログインのユーザーを作成
        $user = $this->createUser();

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
        $response->assertSee('退勤');

        // 退勤済のリクエストを送信
        $response = $this->post('/attendance', [
            'end_work' => Attendance::STATUS_FINISHED,
        ]);

        $response = $this->get('/attendance');
        $response->assertSee('退勤済');
    }

    public function test_finished_work_time_on_admin_page()
    {
        // ログインのユーザーを作成
        $user = $this->createUser();

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

        // 出勤時間を設定
        Carbon::setTestNow(Carbon::create(2025, 1, 20, 9, 0, 0));
        $startWorkTime = now();

        // 勤務中のレコードを作成するリクエストを送信
        $response = $this->post('/attendance', [
            'start_work' => Attendance::STATUS_WORKING,
        ]);

        $response = $this->get('/attendance');
        $response->assertSee('退勤');

        // 退勤時間を現在時刻から540分後に計算
        $endWorkTime = $startWorkTime->copy()->addMinutes(540);
        Carbon::setTestNow($endWorkTime);

        // 退勤済のリクエストを送信
        $response = $this->post('/attendance', [
            'end_work' => Attendance::STATUS_FINISHED,
        ]);

        $response = $this->get('/attendance');
        $response->assertSee('退勤済');

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

        // 退勤時間が表示されることを確認
        $expectedEndWorkTime = $endWorkTime->format('H:i');
        $response->assertSee($expectedEndWorkTime);
    }
}
