<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminStaffListTest extends TestCase
{
    use DatabaseTransactions;

    private function createUser1()
    {
        return User::create([
            'role_id' => User::ROLE_GENERAL,
            'name' => 'テストユーザー1',
            'email' => 'registered01@example.com',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
        ]);
    }

    private function createUser2()
    {
        return User::create([
            'role_id' => User::ROLE_GENERAL,
            'name' => 'テストユーザー2',
            'email' => 'registered02@example.com',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
        ]);
    }

    private function createAdminUser()
    {
        return User::create([
            'role_id' => User::ROLE_ADMIN,
            'name' => '管理者ユーザー',
            'email' => 'admin@example.com',
            'password' => Hash::make('adminpassword123'),
            'email_verified_at' => now(),
        ]);
    }

    private function createAttendance($userId)
    {
        return Attendance::create([
            'user_id' => $userId,
            'date' => Carbon::now()->toDateString(),
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
            'working_hours' => 540,
            'attendance_status_id' => Attendance::STATUS_FINISHED,
        ]);
    }

    private function createBreakTime($attendanceId)
    {
        return BreakTime::create([
            'attendance_id' => $attendanceId,
            'start_time' => '12:00:00',
            'end_time' => '13:00:00',
            'break_time' => '60',
        ]);
    }

    public function test_staff_list()
    {
        // 一般ユーザーを作成
        $user1 = $this->createUser1();
        $user2 = $this->createUser2();

        // 管理者ユーザーを作成
        $adminUser = $this->createAdminUser();

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

        // スタッフ一覧画面にアクセス
        $response = $this->get('/admin/staff/list');
        $response->assertStatus(200);

        $response->assertSeeInOrder([
            $user1->name,
            $user1->email,
            '詳細',
            $user2->name,
            $user2->email,
            '詳細',
        ]);
    }

    public function test_staff_current_month_attendance_list()
    {
        // 一般ユーザーを作成
        $user = $this->createUser1();

        // 管理者ユーザーを作成
        $adminUser = $this->createAdminUser();

        // 勤怠データを作成
        $attendance = $this->createAttendance($user->id);

        $breakTime = $this->createBreakTime($attendance->id);

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

        // スタッフ一覧画面にアクセス
        $response = $this->get('/admin/staff/list');
        $response->assertStatus(200);

        $response->assertSee('詳細');
        $response = $this->get('/admin/attendance/staff/' . $user->id);

        $response->assertSeeInOrder([
            $user->name,
            Carbon::now()->format('Y/n'),
            Carbon::parse($attendance->date)->locale('ja')->isoFormat('M/D(ddd)'),
            Carbon::parse($attendance->start_time)->format('H:i'),
            Carbon::parse($attendance->end_time)->format('H:i'),
            Carbon::parse('00:00')->addMinutes($breakTime->break_time)->format('H:i'),
            Carbon::parse('00:00')->addMinutes($attendance->working_hours)->format('H:i'),
            '詳細',
        ]);
    }

    public function test_staff_previous_month_attendance_list()
    {
        // 現在の月と前月を取得
        $currentMonth = Carbon::now();
        $previousMonth = $currentMonth->copy()->subMonth();

        // 前月のフォーマットを修正
        $previousMonthFormatted = $previousMonth->format('Y-m');

        // 一般ユーザーを作成
        $user = $this->createUser1();

        // 管理者ユーザーを作成
        $adminUser = $this->createAdminUser();

        // 前月の勤怠データを作成
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => $previousMonth->toDateString(),
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
            'working_hours' => 540,
            'attendance_status_id' => Attendance::STATUS_FINISHED,
        ]);

        $breakTime = $this->createBreakTime($attendance->id);

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

        // スタッフ一覧画面にアクセス
        $response = $this->get('/admin/staff/list');
        $response->assertStatus(200);

        $response->assertSee('詳細');
        $response = $this->get('/admin/attendance/staff/' . $user->id . '?month=' . $previousMonthFormatted);

        $response->assertSeeInOrder([
            $user->name,
            Carbon::parse($previousMonthFormatted)->format('Y/n'),
            Carbon::parse($attendance->date)->locale('ja')->isoFormat('M/D(ddd)'),
            Carbon::parse($attendance->start_time)->format('H:i'),
            Carbon::parse($attendance->end_time)->format('H:i'),
            Carbon::parse('00:00')->addMinutes($breakTime->break_time)->format('H:i'),
            Carbon::parse('00:00')->addMinutes($attendance->working_hours)->format('H:i'),
            '詳細',
        ]);
    }

    public function test_staff_next_month_attendance_list()
    {
        // 現在の月と翌月を取得
        $currentMonth = Carbon::now();
        $nextMonth = $currentMonth->copy()->addMonth();

        // 翌月のフォーマットを修正
        $nextMonthFormatted = $nextMonth->format('Y-m');

        // 一般ユーザーを作成
        $user = $this->createUser1();

        // 管理者ユーザーを作成
        $adminUser = $this->createAdminUser();

        // 翌月の勤怠データを作成
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => $nextMonth->toDateString(),
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
            'working_hours' => 540,
            'attendance_status_id' => Attendance::STATUS_FINISHED,
        ]);

        $breakTime = $this->createBreakTime($attendance->id);

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

        // スタッフ一覧画面にアクセス
        $response = $this->get('/admin/staff/list');
        $response->assertStatus(200);

        $response->assertSee('詳細');
        $response = $this->get('/admin/attendance/staff/' . $user->id . '?month=' . $nextMonthFormatted);

        $response->assertSeeInOrder([
            $user->name,
            Carbon::parse($nextMonthFormatted)->format('Y/n'),
            Carbon::parse($attendance->date)->locale('ja')->isoFormat('M/D(ddd)'),
            Carbon::parse($attendance->start_time)->format('H:i'),
            Carbon::parse($attendance->end_time)->format('H:i'),
            Carbon::parse('00:00')->addMinutes($breakTime->break_time)->format('H:i'),
            Carbon::parse('00:00')->addMinutes($attendance->working_hours)->format('H:i'),
            '詳細',
        ]);
    }

    public function test_transition_to_attendance_detail()
    {
        // 一般ユーザーを作成
        $user = $this->createUser1();

        // 管理者ユーザーを作成
        $adminUser = $this->createAdminUser();

        // 勤怠データを作成
        $attendance = $this->createAttendance($user->id);

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

        // スタッフ一覧画面にアクセス
        $response = $this->get('/admin/staff/list');
        $response->assertStatus(200);

        $response->assertSeeInOrder([
            $user->name,
            $user->email,
            '詳細',
        ]);

        // 勤怠詳細画面にアクセス
        $response = $this->get('/attendance/' . $attendance->id);
        $response->assertStatus(200);
    }
}
