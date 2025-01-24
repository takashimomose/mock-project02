<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminAttendanceListTest extends TestCase
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

    private function createAttendance1($userId)
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

    private function createAttendance2($userId)
    {
        return Attendance::create([
            'user_id' => $userId,
            'date' => Carbon::now()->toDateString(),
            'start_time' => '10:00:00',
            'end_time' => '19:00:00',
            'working_hours' => 540,
            'attendance_status_id' => Attendance::STATUS_FINISHED,
        ]);
    }

    private function createBreakTime1($attendanceId)
    {
        return BreakTime::create([
            'attendance_id' => $attendanceId,
            'start_time' => '12:00:00',
            'end_time' => '13:00:00',
            'break_time' => '60',
        ]);
    }

    private function createBreakTime2($attendanceId)
    {
        return BreakTime::create([
            'attendance_id' => $attendanceId,
            'start_time' => '13:00:00',
            'end_time' => '15:00:00',
            'break_time' => '120',
        ]);
    }

    public function test_today_attendance_list()
    {
        // 一般ユーザーを作成
        $user1 = $this->createUser1();
        $user2 = $this->createUser2();

        // 管理者ユーザーを作成
        $adminUser = $this->createAdminUser();

        // 勤怠データを作成
        $attendance1 = $this->createAttendance1($user1->id);
        $attendance2 = $this->createAttendance2($user2->id);

        // 休憩データを作成
        $breakTime1 = $this->createBreakTime1($attendance1->id);
        $breakTime2 = $this->createBreakTime2($attendance2->id);

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

        // 申請一覧画面にアクセス
        $response = $this->get('/admin/attendance/list');
        $response->assertStatus(200);

        $response->assertSeeInOrder([
            Carbon::now()->format('Y年m月d日'),
            $user1->name,
            Carbon::parse($attendance1->start_time)->format('H:i'),
            Carbon::parse($attendance1->end_time)->format('H:i'),
            Carbon::parse('00:00')->addMinutes($breakTime1->break_time)->format('H:i'),
            Carbon::parse('00:00')->addMinutes($attendance1->working_hours)->format('H:i'),
            '詳細',
            $user2->name,
            Carbon::parse($attendance2->start_time)->format('H:i'),
            Carbon::parse($attendance2->end_time)->format('H:i'),
            Carbon::parse('00:00')->addMinutes($breakTime2->break_time)->format('H:i'),
            Carbon::parse('00:00')->addMinutes($attendance2->working_hours)->format('H:i'),
            '詳細',
        ]);
    }

    public function test_the_previous_day_attendance_list()
    {
        // 現在の日と前日を取得
        $currentDate = Carbon::now();
        $previousDate = $currentDate->copy()->subDay();

        // 前日のフォーマットを修正
        $previousDateFormatted = $previousDate->format('Y-m-d');

        // 一般ユーザーを作成
        $user1 = $this->createUser1();
        $user2 = $this->createUser2();

        // 管理者ユーザーを作成
        $adminUser = $this->createAdminUser();

        // 勤怠データを作成
        $attendance1 = Attendance::create([
            'user_id' => $user1->id,
            'date' => Carbon::now()->subDay()->toDateString(),
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
            'working_hours' => 540,
            'attendance_status_id' => Attendance::STATUS_FINISHED,
        ]);

        $attendance2 = Attendance::create([
            'user_id' => $user2->id,
            'date' => Carbon::now()->subDay()->toDateString(),
            'start_time' => '10:00:00',
            'end_time' => '19:00:00',
            'working_hours' => 540,
            'attendance_status_id' => Attendance::STATUS_FINISHED,
        ]);

        // 休憩データを作成
        $breakTime1 = $this->createBreakTime1($attendance1->id);
        $breakTime2 = $this->createBreakTime2($attendance2->id);

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

        // 申請一覧画面にアクセス
        $response = $this->get('/admin/attendance/list');
        $response->assertSee('前日');

        $response = $this->get('/admin/attendance/list?date=' . $previousDateFormatted);
        $response->assertSeeInOrder([
            Carbon::now()->subDay()->format('Y年m月d日'),
            $user1->name,
            Carbon::parse($attendance1->start_time)->format('H:i'),
            Carbon::parse($attendance1->end_time)->format('H:i'),
            Carbon::parse('00:00')->addMinutes($breakTime1->break_time)->format('H:i'),
            Carbon::parse('00:00')->addMinutes($attendance1->working_hours)->format('H:i'),
            '詳細',
            $user2->name,
            Carbon::parse($attendance2->start_time)->format('H:i'),
            Carbon::parse($attendance2->end_time)->format('H:i'),
            Carbon::parse('00:00')->addMinutes($breakTime2->break_time)->format('H:i'),
            Carbon::parse('00:00')->addMinutes($attendance2->working_hours)->format('H:i'),
            '詳細',
        ]);
    }

    public function test_the_next_day_attendance_list()
    {
        // 現在の日と翌日を取得
        $currentDate = Carbon::now();
        $nextDate = $currentDate->copy()->addDay();

        // 翌日のフォーマットを修正
        $nextDateFormatted = $nextDate->format('Y-m-d');

        // 一般ユーザーを作成
        $user1 = $this->createUser1();
        $user2 = $this->createUser2();

        // 管理者ユーザーを作成
        $adminUser = $this->createAdminUser();

        // 勤怠データを作成
        $attendance1 = Attendance::create([
            'user_id' => $user1->id,
            'date' => Carbon::now()->addDay()->toDateString(),
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
            'working_hours' => 540,
            'attendance_status_id' => Attendance::STATUS_FINISHED,
        ]);

        $attendance2 = Attendance::create([
            'user_id' => $user2->id,
            'date' => Carbon::now()->addDay()->toDateString(),
            'start_time' => '10:00:00',
            'end_time' => '19:00:00',
            'working_hours' => 540,
            'attendance_status_id' => Attendance::STATUS_FINISHED,
        ]);

        // 休憩データを作成
        $breakTime1 = $this->createBreakTime1($attendance1->id);
        $breakTime2 = $this->createBreakTime2($attendance2->id);

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

        // 申請一覧画面にアクセス
        $response = $this->get('/admin/attendance/list');
        $response->assertSee('翌日');

        $response = $this->get('/admin/attendance/list?date=' . $nextDateFormatted);
        $response->assertSeeInOrder([
            Carbon::now()->addDay()->format('Y年m月d日'),
            $user1->name,
            Carbon::parse($attendance1->start_time)->format('H:i'),
            Carbon::parse($attendance1->end_time)->format('H:i'),
            Carbon::parse('00:00')->addMinutes($breakTime1->break_time)->format('H:i'),
            Carbon::parse('00:00')->addMinutes($attendance1->working_hours)->format('H:i'),
            '詳細',
            $user2->name,
            Carbon::parse($attendance2->start_time)->format('H:i'),
            Carbon::parse($attendance2->end_time)->format('H:i'),
            Carbon::parse('00:00')->addMinutes($breakTime2->break_time)->format('H:i'),
            Carbon::parse('00:00')->addMinutes($attendance2->working_hours)->format('H:i'),
            '詳細',
        ]);
    }
}
