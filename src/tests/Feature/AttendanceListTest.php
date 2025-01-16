<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AttendanceListTest extends TestCase
{
    use DatabaseTransactions;

    public function test_attendance_list()
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

        // 勤怠データを作成（現在の月のデータを2件作成）
        $attendance1 = Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::now()->startOfMonth()->toDateString(),
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
            'working_hours' => 480,
            'attendance_status_id' => Attendance::STATUS_FINISHED,
        ]);

        $attendance2 = Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::now()->startOfMonth()->addDay()->toDateString(),
            'start_time' => '10:00:00',
            'end_time' => '19:00:00',
            'working_hours' => 450,
            'attendance_status_id' => Attendance::STATUS_FINISHED,
        ]);

        // 対応する休憩データを作成（複数の休憩時間）
        BreakTime::create([
            'attendance_id' => $attendance1->id,
            'break_time' => 30,
        ]);
        BreakTime::create([
            'attendance_id' => $attendance1->id,
            'break_time' => 15,
        ]);

        BreakTime::create([
            'attendance_id' => $attendance2->id,
            'break_time' => 45,
        ]);
        BreakTime::create([
            'attendance_id' => $attendance2->id,
            'break_time' => 20,
        ]);

        // /attendance/list にアクセス
        $response = $this->get('/attendance/list');
        $response->assertStatus(200);

        // 勤怠データがレスポンスに含まれているか確認（1件目）
        $response->assertSee('09:00'); // 出勤時間
        $response->assertSee('18:00'); // 退勤時間
        $response->assertSee('08:00'); // 勤務時間
        $response->assertSee('00:45'); // 休憩時間

        // 勤怠データがレスポンスに含まれているか確認（2件目）
        $response->assertSee('10:00');
        $response->assertSee('19:00');
        $response->assertSee('07:30');
        $response->assertSee('01:05');
    }

    public function test_current_month()
    {
        $currentMonth = Carbon::now()->format('Y/n');

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

        // /attendance/list にアクセス
        $response = $this->get('/attendance/list');
        $response->assertStatus(200);

        $response->assertSee($currentMonth);
    }

    public function test_previous_month_attendance()
    {
        // 現在の月と前月を取得
        $currentMonth = Carbon::now();
        $previousMonth = $currentMonth->copy()->subMonth();

        // 前月のフォーマットを修正
        $previousMonthFormatted = $previousMonth->format('Y-m');

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

        // 勤怠データを作成（前月のデータを2件作成）
        $attendance1 = Attendance::create([
            'user_id' => $user->id,
            'date' => $previousMonth->startOfMonth()->toDateString(),
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
            'working_hours' => 480,
            'attendance_status_id' => Attendance::STATUS_FINISHED,
        ]);

        $attendance2 = Attendance::create([
            'user_id' => $user->id,
            'date' => $previousMonth->startOfMonth()->addDay()->toDateString(),
            'start_time' => '10:00:00',
            'end_time' => '19:00:00',
            'working_hours' => 450,
            'attendance_status_id' => Attendance::STATUS_FINISHED,
        ]);

        // 対応する休憩データを作成（複数の休憩時間）
        BreakTime::create([
            'attendance_id' => $attendance1->id,
            'break_time' => 30,
        ]);
        BreakTime::create([
            'attendance_id' => $attendance1->id,
            'break_time' => 15,
        ]);

        BreakTime::create([
            'attendance_id' => $attendance2->id,
            'break_time' => 45,
        ]);
        BreakTime::create([
            'attendance_id' => $attendance2->id,
            'break_time' => 20,
        ]);

        $response = $this->get('/attendance/list');
        $response->assertSee('前月');

        $response = $this->get('/attendance/list?month=' . $previousMonthFormatted);
        $response->assertStatus(200);

        // 勤怠データがレスポンスに含まれているか確認（1件目）
        $response->assertSee('09:00'); // 出勤時間
        $response->assertSee('18:00'); // 退勤時間
        $response->assertSee('08:00'); // 勤務時間
        $response->assertSee('00:45'); // 休憩時間

        // 勤怠データがレスポンスに含まれているか確認（2件目）
        $response->assertSee('10:00');
        $response->assertSee('19:00');
        $response->assertSee('07:30');
        $response->assertSee('01:05');
    }

    public function test_next_month_attendance()
    {
        // 現在の月と前月を取得
        $currentMonth = Carbon::now();
        $nextMonth = $currentMonth->copy()->addMonth();

        // 前月のフォーマットを修正
        $nextMonthFormatted = $nextMonth->format('Y-m');

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

        // 勤怠データを作成（前月のデータを2件作成）
        $attendance1 = Attendance::create([
            'user_id' => $user->id,
            'date' => $nextMonth->startOfMonth()->toDateString(),
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
            'working_hours' => 480,
            'attendance_status_id' => Attendance::STATUS_FINISHED,
        ]);

        $attendance2 = Attendance::create([
            'user_id' => $user->id,
            'date' => $nextMonth->startOfMonth()->addDay()->toDateString(),
            'start_time' => '10:00:00',
            'end_time' => '19:00:00',
            'working_hours' => 450,
            'attendance_status_id' => Attendance::STATUS_FINISHED,
        ]);

        // 対応する休憩データを作成（複数の休憩時間）
        BreakTime::create([
            'attendance_id' => $attendance1->id,
            'break_time' => 30,
        ]);
        BreakTime::create([
            'attendance_id' => $attendance1->id,
            'break_time' => 15,
        ]);

        BreakTime::create([
            'attendance_id' => $attendance2->id,
            'break_time' => 45,
        ]);
        BreakTime::create([
            'attendance_id' => $attendance2->id,
            'break_time' => 20,
        ]);

        $response = $this->get('/attendance/list');
        $response->assertSee('翌月');

        $response = $this->get('/attendance/list?month=' . $nextMonthFormatted);
        $response->assertStatus(200);

        // 勤怠データがレスポンスに含まれているか確認（1件目）
        $response->assertSee('09:00'); // 出勤時間
        $response->assertSee('18:00'); // 退勤時間
        $response->assertSee('08:00'); // 勤務時間
        $response->assertSee('00:45'); // 休憩時間

        // 勤怠データがレスポンスに含まれているか確認（2件目）
        $response->assertSee('10:00');
        $response->assertSee('19:00');
        $response->assertSee('07:30');
        $response->assertSee('01:05');
    }

    public function test_attendance_detail_button()
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

        // 勤怠データを作成
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::now()->startOfMonth()->toDateString(),
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
            'working_hours' => 480,
            'attendance_status_id' => Attendance::STATUS_FINISHED,
        ]);

        $response = $this->get('/attendance/list');
        $response->assertSee('詳細');

        $response = $this->get('/attendance/' . $attendance->id);
        $response->assertStatus(200);
    }
}
