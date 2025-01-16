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

        // 勤務中のレコードを作成
        Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::today()->toDateString(),
            'start_time' => '09:00:00',
            'attendance_status_id' => Attendance::STATUS_WORKING,
        ]);

        $response = $this->get('/attendance');
        $response->assertSee('休憩入');

        // break_times テーブルにレコードを挿入
        BreakTime::create([
            'attendance_id' => Attendance::where('user_id', $user->id)
                ->where('date', Carbon::today()->toDateString())
                ->value('id'),
            'start_time' => '12:00:00',
        ]);

        // attendances テーブルの attendance_status_id を STATUS_BREAK に更新
        $attendance = Attendance::where('user_id', $user->id)
            ->where('date', Carbon::today()->toDateString())
            ->first();

        $attendance->update([
            'attendance_status_id' => Attendance::STATUS_BREAK,
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

        // 勤務中のレコードを作成
        Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::today()->toDateString(),
            'start_time' => '09:00:00',
            'attendance_status_id' => Attendance::STATUS_WORKING,
        ]);

        $response = $this->get('/attendance');
        $response->assertSee('休憩入');

        // break_times テーブルにレコードを挿入
        BreakTime::create([
            'attendance_id' => Attendance::where('user_id', $user->id)
                ->where('date', Carbon::today()->toDateString())
                ->value('id'),
            'start_time' => '12:00:00',
        ]);

        // attendances テーブルの attendance_status_id を STATUS_BREAK に更新
        $attendance = Attendance::where('user_id', $user->id)
            ->where('date', Carbon::today()->toDateString())
            ->first();

        $attendance->update([
            'attendance_status_id' => Attendance::STATUS_BREAK,
        ]);

        $response = $this->get('/attendance');
        $response->assertSee('休憩戻');

        // break_times テーブルにレコードを挿入
        BreakTime::create([
            'attendance_id' => Attendance::where('user_id', $user->id)
                ->where('date', Carbon::today()->toDateString())
                ->value('id'),
            'end_time' => '13:00:00',
        ]);

        // attendances テーブルの attendance_status_id を STATUS_WORKING に更新
        $attendance = Attendance::where('user_id', $user->id)
            ->where('date', Carbon::today()->toDateString())
            ->first();

        $attendance->update([
            'attendance_status_id' => Attendance::STATUS_WORKING,
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

        // 勤務中のレコードを作成
        Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::today()->toDateString(),
            'start_time' => '09:00:00',
            'attendance_status_id' => Attendance::STATUS_WORKING,
        ]);

        $response = $this->get('/attendance');
        $response->assertSee('休憩入');

        // break_times テーブルにレコードを挿入
        BreakTime::create([
            'attendance_id' => Attendance::where('user_id', $user->id)
                ->where('date', Carbon::today()->toDateString())
                ->value('id'),
            'start_time' => '12:00:00',
        ]);

        // attendances テーブルの attendance_status_id を STATUS_BREAK に更新
        $attendance = Attendance::where('user_id', $user->id)
            ->where('date', Carbon::today()->toDateString())
            ->first();

        $attendance->update([
            'attendance_status_id' => Attendance::STATUS_BREAK,
        ]);

        $response = $this->get('/attendance');
        $response->assertSee('休憩戻');

        // break_times テーブルにレコードを挿入
        BreakTime::create([
            'attendance_id' => Attendance::where('user_id', $user->id)
                ->where('date', Carbon::today()->toDateString())
                ->value('id'),
            'end_time' => '13:00:00',
        ]);

        // attendances テーブルの attendance_status_id を STATUS_WORKING に更新
        $attendance = Attendance::where('user_id', $user->id)
            ->where('date', Carbon::today()->toDateString())
            ->first();

        $attendance->update([
            'attendance_status_id' => Attendance::STATUS_WORKING,
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

        // 勤務中のレコードを作成
        Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::today()->toDateString(),
            'start_time' => '09:00:00',
            'attendance_status_id' => Attendance::STATUS_WORKING,
        ]);

        $response = $this->get('/attendance');
        $response->assertSee('休憩入');

        // break_times テーブルにレコードを挿入
        BreakTime::create([
            'attendance_id' => Attendance::where('user_id', $user->id)
                ->where('date', Carbon::today()->toDateString())
                ->value('id'),
            'start_time' => '12:00:00',
        ]);

        // attendances テーブルの attendance_status_id を STATUS_BREAK に更新
        $attendance = Attendance::where('user_id', $user->id)
            ->where('date', Carbon::today()->toDateString())
            ->first();

        $attendance->update([
            'attendance_status_id' => Attendance::STATUS_BREAK,
        ]);

        $response = $this->get('/attendance');
        $response->assertSee('休憩戻');

        // break_times テーブルにレコードを挿入
        BreakTime::create([
            'attendance_id' => Attendance::where('user_id', $user->id)
                ->where('date', Carbon::today()->toDateString())
                ->value('id'),
            'end_time' => '13:00:00',
        ]);

        // attendances テーブルの attendance_status_id を STATUS_WORKING に更新
        $attendance = Attendance::where('user_id', $user->id)
            ->where('date', Carbon::today()->toDateString())
            ->first();

        $attendance->update([
            'attendance_status_id' => Attendance::STATUS_WORKING,
        ]);

        $response = $this->get('/attendance');
        $response->assertSee('休憩入');

        // break_times テーブルにレコードを挿入
        BreakTime::create([
            'attendance_id' => Attendance::where('user_id', $user->id)
                ->where('date', Carbon::today()->toDateString())
                ->value('id'),
            'start_time' => '14:00:00',
        ]);

        // attendances テーブルの attendance_status_id を STATUS_BREAK に更新
        $attendance = Attendance::where('user_id', $user->id)
            ->where('date', Carbon::today()->toDateString())
            ->first();

        $attendance->update([
            'attendance_status_id' => Attendance::STATUS_BREAK,
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

        // 勤務中のレコードを作成
        Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::today()->toDateString(),
            'start_time' => '09:00:00',
            'attendance_status_id' => Attendance::STATUS_WORKING,
        ]);

        $response = $this->get('/attendance');
        $response->assertSee('休憩入');

        // break_times テーブルにレコードを挿入
        BreakTime::create([
            'attendance_id' => Attendance::where('user_id', $user->id)
                ->where('date', Carbon::today()->toDateString())
                ->value('id'),
            'start_time' => '12:00:00',
        ]);

        // attendances テーブルの attendance_status_id を STATUS_BREAK に更新
        $attendance = Attendance::where('user_id', $user->id)
            ->where('date', Carbon::today()->toDateString())
            ->first();

        $attendance->update([
            'attendance_status_id' => Attendance::STATUS_BREAK,
        ]);

        $response = $this->get('/attendance');
        $response->assertSee('休憩戻');

        // break_times テーブルにレコードを挿入
        BreakTime::create([
            'attendance_id' => Attendance::where('user_id', $user->id)
                ->where('date', Carbon::today()->toDateString())
                ->value('id'),
            'end_time' => '13:00:00',
        ]);

        // attendances テーブルの attendance_status_id を STATUS_WORKING に更新
        $attendance = Attendance::where('user_id', $user->id)
            ->where('date', Carbon::today()->toDateString())
            ->first();

        $attendance->update([
            'attendance_status_id' => Attendance::STATUS_WORKING,
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

        // 休憩時間 が 01:00として表示されることを確認
        $response->assertSee('01:00');
    }
}
