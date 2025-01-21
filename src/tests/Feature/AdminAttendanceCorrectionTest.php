<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\AttendanceCorrection;
use App\Models\BreakTime;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminAttendanceCorrectionTest extends TestCase
{
    use DatabaseTransactions;

    public function test_pending_request_list()
    {
        // ユーザーを作成
        $user1 = User::create([
            'role_id' => User::ROLE_GENERAL,
            'name' => 'テストユーザー',
            'email' => 'registered@example.com',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
        ]);

        $user2 = User::create([
            'role_id' => User::ROLE_GENERAL,
            'name' => 'テストユーザー2',
            'email' => 'registered02@example.com',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
        ]);

        // 管理者ユーザーを作成
        $adminUser = User::create([
            'role_id' => User::ROLE_ADMIN,
            'name' => '管理者ユーザー',
            'email' => 'admin@example.com',
            'password' => Hash::make('adminpassword123'),
        ]);

        // 勤怠データをユーザー別に1つずつ作成
        $attendance1 = Attendance::create([
            'user_id' => $user1->id,
            'date' => Carbon::now()->toDateString(),
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
            'working_hours' => 480,
            'attendance_status_id' => Attendance::STATUS_FINISHED,
        ]);

        $attendance2 = Attendance::create([
            'user_id' => $user2->id,
            'date' => Carbon::now()->toDateString(),
            'start_time' => '10:00:00',
            'end_time' => '19:00:00',
            'working_hours' => 480,
            'attendance_status_id' => Attendance::STATUS_FINISHED,
        ]);

        // 勤怠修正リクエストのデータをユーザー別に1つずつ作成
        $attendanceCorrection1 = AttendanceCorrection::create([
            'date' => Carbon::now()->toDateString(),
            'start_time' => '08:00',
            'end_time' => '17:00',
            'attendance_id' => $attendance1->id,
            'request_date' => Carbon::now(),
            'correction_status_id' => AttendanceCorrection::PENDING,
            'reason' => 'テストユーザーです。勤怠を間違えたため修正お願いします。',
        ]);

        $attendanceCorrection2 = AttendanceCorrection::create([
            'date' => Carbon::now()->toDateString(),
            'start_time' => '07:30',
            'end_time' => '16:30',
            'attendance_id' => $attendance2->id,
            'request_date' => Carbon::now(),
            'correction_status_id' => AttendanceCorrection::PENDING,
            'reason' => 'テストユーザー2です。勤怠を間違えたため修正お願いします。',
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

        // 申請一覧画面（承認待ちタブ）にアクセス
        $response = $this->get('/stamp_correction_request/list');

        $response->assertSeeInOrder([
            '承認待ち',
            $user1->name,
            Carbon::parse($attendance1->date)->format('n月j日'),
            'テストユーザーです。勤怠を間違えたため修正お願いします。',
            Carbon::parse($attendanceCorrection1->request_date)->format('n月j日'),
            '詳細',
            '承認待ち',
            $user2->name,
            Carbon::parse($attendance2->date)->format('n月j日'),
            'テストユーザー2です。勤怠を間違えたため修正お願いします。',
            Carbon::parse($attendanceCorrection2->request_date)->format('n月j日'),
            '詳細',
        ]);
    }

    public function test_approved_request_list()
    {
        // ユーザーを作成
        $user1 = User::create([
            'role_id' => User::ROLE_GENERAL,
            'name' => 'テストユーザー',
            'email' => 'registered@example.com',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
        ]);

        $user2 = User::create([
            'role_id' => User::ROLE_GENERAL,
            'name' => 'テストユーザー2',
            'email' => 'registered02@example.com',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
        ]);

        // 管理者ユーザーを作成
        $adminUser = User::create([
            'role_id' => User::ROLE_ADMIN,
            'name' => '管理者ユーザー',
            'email' => 'admin@example.com',
            'password' => Hash::make('adminpassword123'),
        ]);

        // 勤怠データをユーザー別に1つずつ作成
        $attendance1 = Attendance::create([
            'user_id' => $user1->id,
            'date' => Carbon::now()->toDateString(),
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
            'working_hours' => 480,
            'attendance_status_id' => Attendance::STATUS_FINISHED,
        ]);

        $attendance2 = Attendance::create([
            'user_id' => $user2->id,
            'date' => Carbon::now()->toDateString(),
            'start_time' => '10:00:00',
            'end_time' => '19:00:00',
            'working_hours' => 480,
            'attendance_status_id' => Attendance::STATUS_FINISHED,
        ]);

        // 勤怠修正リクエストのデータをユーザー別に1つずつ作成
        $attendanceCorrection1 = AttendanceCorrection::create([
            'date' => Carbon::now()->toDateString(),
            'start_time' => '08:00',
            'end_time' => '17:00',
            'attendance_id' => $attendance1->id,
            'request_date' => Carbon::now(),
            'correction_status_id' => AttendanceCorrection::APPROVED,
            'reason' => 'テストユーザーです。勤怠を間違えたため修正お願いします。',
        ]);

        $attendanceCorrection2 = AttendanceCorrection::create([
            'date' => Carbon::now()->toDateString(),
            'start_time' => '07:30',
            'end_time' => '16:30',
            'attendance_id' => $attendance2->id,
            'request_date' => Carbon::now(),
            'correction_status_id' => AttendanceCorrection::APPROVED,
            'reason' => 'テストユーザー2です。勤怠を間違えたため修正お願いします。',
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

        // 申請一覧画面（承認待ちタブ）にアクセス
        $response = $this->get('/stamp_correction_request/list?tab=approved');

        $response->assertSeeInOrder([
            '承認済み',
            $user1->name,
            Carbon::parse($attendance1->date)->format('n月j日'),
            'テストユーザーです。勤怠を間違えたため修正お願いします。',
            Carbon::parse($attendanceCorrection1->request_date)->format('n月j日'),
            '詳細',
            '承認済み',
            $user2->name,
            Carbon::parse($attendance2->date)->format('n月j日'),
            'テストユーザー2です。勤怠を間違えたため修正お願いします。',
            Carbon::parse($attendanceCorrection2->request_date)->format('n月j日'),
            '詳細',
        ]);
    }

    public function test_attendance_detail_from_request_list()
    {
        // ログインのユーザーを作成
        $user = User::create([
            'role_id' => User::ROLE_GENERAL,
            'name' => 'テストユーザー',
            'email' => 'registered@example.com',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
        ]);

        // 管理者ユーザーを作成
        $adminUser = User::create([
            'role_id' => User::ROLE_ADMIN,
            'name' => '管理者ユーザー',
            'email' => 'admin@example.com',
            'password' => Hash::make('adminpassword123'),
        ]);

        // 勤怠データを作成
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::now()->toDateString(),
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
            'working_hours' => 480,
            'attendance_status_id' => Attendance::STATUS_FINISHED,
        ]);

        // 勤怠修正リクエストのデータを作成
        $attendanceCorrection = AttendanceCorrection::create([
            'date' => Carbon::now()->toDateString(),
            'start_time' => '08:00',
            'end_time' => '17:00',
            'attendance_id' => $attendance->id,
            'request_date' => Carbon::now(),
            'correction_status_id' => AttendanceCorrection::APPROVED,
            'reason' => 'テストユーザーです。勤怠を間違えたため修正お願いします。',
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

        // 申請一覧画面（承認待ちタブ）にアクセス
        $response = $this->get('/stamp_correction_request/list');
        $response->assertSee('詳細');

        // 申請詳細画面（勤怠詳細画面）にアクセス
        $response = $this->get('/stamp_correction_request/approve/' . $attendanceCorrection->id);
        $response->assertStatus(200);
    }

    public function test_aprrove_correction_request()
    {
        // ログインのユーザーを作成
        $user = User::create([
            'role_id' => User::ROLE_GENERAL,
            'name' => 'テストユーザー',
            'email' => 'registered@example.com',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
        ]);

        // 管理者ユーザーを作成
        $adminUser = User::create([
            'role_id' => User::ROLE_ADMIN,
            'name' => '管理者ユーザー',
            'email' => 'admin@example.com',
            'password' => Hash::make('adminpassword123'),
        ]);

        // 勤怠データを作成
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::now()->toDateString(),
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
            'working_hours' => 480,
            'attendance_status_id' => Attendance::STATUS_FINISHED,
        ]);

        // 勤怠修正リクエストのデータを作成
        $attendanceCorrection = AttendanceCorrection::create([
            'date' => Carbon::now()->toDateString(),
            'start_time' => '08:00',
            'end_time' => '17:00',
            'attendance_id' => $attendance->id,
            'request_date' => Carbon::now(),
            'correction_status_id' => AttendanceCorrection::APPROVED,
            'reason' => 'テストユーザーです。勤怠を間違えたため修正お願いします。',
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

        // 申請一覧画面（承認待ちタブ）にアクセス
        $response = $this->get('/stamp_correction_request/list');
        $response->assertSee('詳細');

        // 申請詳細画面にアクセス
        $response = $this->get('/stamp_correction_request/approve/' . $attendanceCorrection->id);
        $response->assertSee('承認');

        //ここで承認を実行
        $response = $this->post('/stamp_correction_request/approve', [
            'attendance_id' => $attendance->id,
            'correction_id' => $attendanceCorrection->id,
            'date_year' => Carbon::parse($attendanceCorrection->date)->format('Y年'),
            'date_day' => Carbon::parse($attendanceCorrection->date)->format('m月d日'),
            'start_time' => $attendanceCorrection->start_time,
            'end_time' => $attendanceCorrection->end_time,
            'reason' => $attendanceCorrection->reason,
        ]);

        $response->assertRedirect('/stamp_correction_request/approve/' . $attendanceCorrection->id);

        // 勤怠詳細画面にアクセス
        $response = $this->get('/attendance/' . $attendance->id);
        $response->assertSeeInOrder([
            $user->name,
            Carbon::parse($attendanceCorrection->date)->format('Y年'),
            Carbon::parse($attendanceCorrection->date)->format('m月d日'),
            Carbon::parse($attendanceCorrection->start_time)->format('H:i'),
            Carbon::parse($attendanceCorrection->end_time)->format('H:i'),
        ]);
    }
}
