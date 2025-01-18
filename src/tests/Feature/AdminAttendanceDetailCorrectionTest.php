<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminAttendanceDetailCorrectionTest extends TestCase
{
    use DatabaseTransactions;

    public function test_attendance_detail()
    {
        // ユーザーを作成
        $user = User::create([
            'role_id' => User::ROLE_GENERAL,
            'name' => 'テストユーザー',
            'email' => 'registered@example.com',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
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

        $breakTime = BreakTime::create([
            'attendance_id' => Attendance::where('user_id', $user->id)
                ->where('date', Carbon::now()->toDateString())
                ->value('id'),
            'start_time' => '12:00:00',
            'end_time' => '13:00:00',
            'break_time' => '60',
        ]);

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

        // 勤怠一覧画面にアクセス
        $response = $this->get('/admin/attendance/list');
        $response->assertSeeInOrder([
            Carbon::now()->format('Y年m月d日'),
            $user->name,
            Carbon::parse($attendance->start_time)->format('H:i'),
            Carbon::parse($attendance->end_time)->format('H:i'),
            Carbon::parse('00:00')->addMinutes($breakTime->break_time)->format('H:i'),
            Carbon::parse('00:00')->addMinutes($attendance->working_hours)->format('H:i'),
            '詳細',
        ]);

        $response = $this->get('/attendance/' . $attendance->id);
        $response->assertSeeInOrder([
            $user->name,
            Carbon::now()->format('Y年'),
            Carbon::now()->format('m月d日'),
            Carbon::parse($attendance->start_time)->format('H:i'),
            Carbon::parse($attendance->end_time)->format('H:i'),
            Carbon::parse($breakTime->start_time)->format('H:i'),
            Carbon::parse($breakTime->end_time)->format('H:i'),
        ]);
    }

    public function test_start_time()
    {
        // ユーザーを作成
        $user = User::create([
            'role_id' => User::ROLE_GENERAL,
            'name' => 'テストユーザー',
            'email' => 'registered@example.com',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
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

        BreakTime::create([
            'attendance_id' => Attendance::where('user_id', $user->id)
                ->where('date', Carbon::now()->toDateString())
                ->value('id'),
            'start_time' => '12:00:00',
            'end_time' => '13:00:00',
            'break_time' => '60',
        ]);

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

        // 勤怠一覧画面にアクセス
        $response = $this->get('/admin/attendance/list');
        $response->assertSee('詳細');

        $response = $this->get('/attendance/' . $attendance->id);

        // 出勤時間を退勤時間より後に入力
        $response = $this->post('/attendance/correct/admin', [
            'start_time' => '19:00',
            'end_time' => '18:00',
        ]);

        // バリデーションメッセージを確認
        $response->assertSessionHasErrors(['start_time_before_end_time' => '出勤時間もしくは退勤時間が不適切な値です']);
    }

    public function test_break_start_time()
    {
        // ユーザーを作成
        $user = User::create([
            'role_id' => User::ROLE_GENERAL,
            'name' => 'テストユーザー',
            'email' => 'registered@example.com',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
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

        BreakTime::create([
            'attendance_id' => Attendance::where('user_id', $user->id)
                ->where('date', Carbon::now()->toDateString())
                ->value('id'),
            'start_time' => '12:00:00',
            'end_time' => '13:00:00',
            'break_time' => '60',
        ]);

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

        // 勤怠一覧画面にアクセス
        $response = $this->get('/admin/attendance/list');
        $response->assertSee('詳細');

        $response = $this->get('/attendance/' . $attendance->id);

        // 休憩開始時間を退勤時間より後に入力
        $response = $this->post('/attendance/correct/admin', [
            'start_time' => '09:00',
            'end_time' => '18:00',
            'break_start_time' => ['0' => '19:00'],
        ]);

        // バリデーションメッセージを確認
        $response->assertSessionHasErrors(['break_time_out_of_range.0' => '休憩時間が勤務時間外です']);
    }

    public function test_break_end_time()
    {
        // ユーザーを作成
        $user = User::create([
            'role_id' => User::ROLE_GENERAL,
            'name' => 'テストユーザー',
            'email' => 'registered@example.com',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
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

        BreakTime::create([
            'attendance_id' => Attendance::where('user_id', $user->id)
                ->where('date', Carbon::now()->toDateString())
                ->value('id'),
            'start_time' => '12:00:00',
            'end_time' => '13:00:00',
            'break_time' => '60',
        ]);

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

        // 勤怠一覧画面にアクセス
        $response = $this->get('/admin/attendance/list');
        $response->assertSee('詳細');

        $response = $this->get('/attendance/' . $attendance->id);

        // 休憩時間を退勤時間より後に入力
        $response = $this->post('/attendance/correct/admin', [
            'start_time' => '09:00',
            'end_time' => '18:00',
            'break_start_time' => ['0' => '19:00'],
            'break_end_time' => ['0' => '20:00'],
        ]);

        // バリデーションメッセージを確認
        $response->assertSessionHasErrors(['break_within_working_hours.0' => '休憩時間が勤務時間外です']);
    }

    public function test_reason()
    {
        // ユーザーを作成
        $user = User::create([
            'role_id' => User::ROLE_GENERAL,
            'name' => 'テストユーザー',
            'email' => 'registered@example.com',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
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

        BreakTime::create([
            'attendance_id' => Attendance::where('user_id', $user->id)
                ->where('date', Carbon::now()->toDateString())
                ->value('id'),
            'start_time' => '12:00:00',
            'end_time' => '13:00:00',
            'break_time' => '60',
        ]);

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

        // 勤怠一覧画面にアクセス
        $response = $this->get('/admin/attendance/list');
        $response->assertSee('詳細');

        $response = $this->get('/attendance/' . $attendance->id);

        // 休憩時間を退勤時間より後に入力
        $response = $this->post('/attendance/correct/admin', [
            'reason' => null,
        ]);

        // バリデーションメッセージを確認
        $response->assertSessionHasErrors(['reason' => '備考を入力してください']);
    }
}
