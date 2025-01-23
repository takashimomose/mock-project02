<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\AttendanceCorrection;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AttendanceCorrectionTest extends TestCase
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
            'date' => Carbon::now()->subDay()->toDateString(),
            'start_time' => '10:00:00',
            'end_time' => '19:00:00',
            'working_hours' => 540,
            'attendance_status_id' => Attendance::STATUS_FINISHED,
        ]);
    }

    private function createAttendanceCorrection1($attendance1)
    {
        return AttendanceCorrection::create([
            'old_date' => $attendance1->date,
            'date' => Carbon::now()->toDateString(),
            'start_time' => '08:00',
            'end_time' => '17:00',
            'attendance_id' => $attendance1->id,
            'request_date' => Carbon::now(),
            'correction_status_id' => AttendanceCorrection::APPROVED,
            'reason' => '勤怠を間違えたため修正お願いします。',
        ]);
    }

    private function createAttendanceCorrection2($attendance2)
    {
        return AttendanceCorrection::create([
            'old_date' => $attendance2->date,
            'date' => Carbon::now()->subDay()->toDateString(),
            'start_time' => '07:30',
            'end_time' => '16:30',
            'attendance_id' => $attendance2->id,
            'request_date' => Carbon::now(),
            'correction_status_id' => AttendanceCorrection::APPROVED,
            'reason' => '昨日の勤怠も修正お願いします。',
        ]);
    }

    public function test_start_time()
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

        // 勤怠データを作成
        $attendance = $this->createAttendance1($user->id);

        $response = $this->get('/attendance/list');
        $response->assertSee('詳細');

        $response = $this->get('/attendance/' . $attendance->id);

        // 出勤時間を退勤時間より後に入力
        $response = $this->post('/attendance/correct/general', [
            'start_time' => '19:00',
            'end_time' => '18:00',
        ]);

        // バリデーションメッセージを確認
        $response->assertSessionHasErrors(['start_time_before_end_time' => '出勤時間もしくは退勤時間が不適切な値です']);
    }

    public function test_break_start_time()
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

        // 勤怠データを作成
        $attendance = $this->createAttendance1($user->id);

        $response = $this->get('/attendance/list');
        $response->assertSee('詳細');

        $response = $this->get('/attendance/' . $attendance->id);

        // 休憩時間を退勤時間より後に入力
        $response = $this->post('/attendance/correct/general', [
            'start_time' => '09:00',
            'end_time' => '18:00',
            'break_start_time' => ['0' => '19:00'],
        ]);

        // バリデーションメッセージを確認
        $response->assertSessionHasErrors(['break_time_out_of_range.0' => '休憩時間が勤務時間外です']);
    }

    public function test_break_end_time()
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

        // 勤怠データを作成
        $attendance = $this->createAttendance1($user->id);

        $response = $this->get('/attendance/list');
        $response->assertSee('詳細');

        $response = $this->get('/attendance/' . $attendance->id);

        // 休憩時間を退勤時間より後に入力
        $response = $this->post('/attendance/correct/general', [
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

        // 勤怠データを作成
        $attendance = $this->createAttendance1($user->id);

        $response = $this->get('/attendance/list');
        $response->assertSee('詳細');

        $response = $this->get('/attendance/' . $attendance->id);

        // 休憩時間を退勤時間より後に入力
        $response = $this->post('/attendance/correct/general', [
            'reason' => null,
        ]);

        // バリデーションメッセージを確認
        $response->assertSessionHasErrors(['reason' => '備考を入力してください']);
    }

    public function test_attendance_correction_request()
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

        // 勤怠データを作成
        $attendance = $this->createAttendance1($user->id);

        $response = $this->get('/attendance/list');
        $response->assertSee('詳細');

        $response = $this->get('/attendance/' . $attendance->id);

        $response = $this->post('/attendance/correct/general', [
            'old_date_year' => Carbon::now()->format('Y年'),
            'old_date_day' => Carbon::now()->format('m月d日'),
            'date_year' => Carbon::now()->format('Y年'),
            'date_day' => Carbon::now()->format('m月d日'),
            'start_time' => '08:00',
            'end_time' => '17:00',
            'attendance_id' => $attendance->id,
            'request_date' => Carbon::now(),
            'correction_status_id' => AttendanceCorrection::PENDING,
            'reason' => '勤怠を間違えたため修正お願いします。'
        ]);

        $response->assertRedirect('/attendance/list');

        // 修正リクエストが保存されたか確認
        $attendanceCorrection = AttendanceCorrection::where('attendance_id', $attendance->id)
            ->where('correction_status_id', AttendanceCorrection::PENDING)
            ->firstOrFail();

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

        // 申請一覧画面にアクセス
        $response = $this->get('/stamp_correction_request/list');
        $response->assertStatus(200);

        $response->assertSee('承認待ち');
        $response->assertSee($user->name);
        $response->assertSee(Carbon::parse($attendance->date)->format('n月j日'));
        $response->assertSee('勤怠を間違えたため修正お願いします。');
        $response->assertSee(Carbon::now()->Format('n月j日'));
        $response->assertSee('詳細');

        //勤怠詳細（承認）画面にアクセス
        $response = $this->get('/stamp_correction_request/approve/' . $attendanceCorrection->id);

        $response->assertSee($user->name);
        $response->assertSee(Carbon::now()->format('Y年'));
        $response->assertSee(Carbon::now()->format('m月d日'));
        $response->assertSee('08:00');
        $response->assertSee('17:00');
        $response->assertSee('勤怠を間違えたため修正お願いします。');
        $response->assertSee('承認');
    }

    public function test_pending_request_list()
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

        // 勤怠データを2つ作成
        $attendance1 = $this->createAttendance1($user->id);
        $attendance2 = $this->createAttendance2($user->id);

        // 勤怠修正リクエストを2つ作成
        $this->post('/attendance/correct/general', [
            'old_date_year' => Carbon::now()->format('Y年'),
            'old_date_day' => Carbon::now()->format('m月d日'),
            'date_year' => Carbon::now()->format('Y年'),
            'date_day' => Carbon::now()->format('m月d日'),
            'start_time' => '08:00',
            'end_time' => '17:00',
            'attendance_id' => $attendance1->id,
            'request_date' => Carbon::now(),
            'correction_status_id' => AttendanceCorrection::PENDING,
            'reason' => '勤怠を間違えたため修正お願いします。',
        ])->assertRedirect('/attendance/list');

        $this->post('/attendance/correct/general', [
            'old_date_year' => Carbon::now()->subDay()->format('Y年'),
            'old_date_day' => Carbon::now()->subDay()->format('m月d日'),
            'date_year' => Carbon::now()->subDay()->format('Y年'),
            'date_day' => Carbon::now()->subDay()->format('m月d日'),
            'start_time' => '07:30',
            'end_time' => '16:30',
            'attendance_id' => $attendance2->id,
            'request_date' => Carbon::now(),
            'correction_status_id' => AttendanceCorrection::PENDING,
            'reason' => '昨日の勤怠も修正お願いします。',
        ])->assertRedirect('/attendance/list');

        // 修正リクエストが保存されたか確認
        AttendanceCorrection::where('attendance_id', $attendance1->id)
            ->where('correction_status_id', AttendanceCorrection::PENDING)
            ->firstOrFail();

        AttendanceCorrection::where('attendance_id', $attendance2->id)
            ->where('correction_status_id', AttendanceCorrection::PENDING)
            ->firstOrFail();

        // 申請一覧画面（承認待ちタブ）にアクセス
        $response = $this->get('/stamp_correction_request/list');
        $response->assertStatus(200);

        $response->assertSeeInOrder([
            '承認待ち',
            $user->name,
            Carbon::parse($attendance1->date)->format('n月j日'),
            '勤怠を間違えたため修正お願いします。',
            Carbon::now()->Format('n月j日'),
            '詳細',
            '承認待ち',
            $user->name,
            Carbon::parse($attendance2->date)->format('n月j日'),
            '昨日の勤怠も修正お願いします。',
            Carbon::now()->Format('n月j日'),
            '詳細',
        ]);
    }

    public function test_approved_request_list()
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

        // 勤怠データを2つ作成
        $attendance1 = $this->createAttendance1($user->id);
        $attendance2 = $this->createAttendance2($user->id);

        // 承認済みの勤怠修正リクエストデータを2つ作成
        $this->createAttendanceCorrection1($attendance1);
        $this->createAttendanceCorrection2($attendance2);

        // 申請一覧画面（承認済みタブ）にアクセス
        $response = $this->get('/stamp_correction_request/list?tab=approved');

        $response->assertSeeInOrder([
            '承認済み',
            $user->name,
            Carbon::parse($attendance1->date)->format('n月j日'),
            '勤怠を間違えたため修正お願いします。',
            Carbon::now()->Format('n月j日'),
            '詳細',
            '承認済み',
            $user->name,
            Carbon::parse($attendance2->date)->format('n月j日'),
            '昨日の勤怠も修正お願いします。',
            Carbon::now()->Format('n月j日'),
            '詳細',
        ]);
    }

    public function test_attendance_detail_from_request_list()
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

        // 勤怠データを作成
        $attendance = $this->createAttendance1($user->id);

        // 勤怠修正リクエストを作成
        $this->post('/attendance/correct/general', [
            'old_date_year' => Carbon::now()->subYear()->format('Y年'),
            'old_date_day' => Carbon::now()->subDay()->format('m月d日'),
            'date_year' => Carbon::now()->format('Y年'),
            'date_day' => Carbon::now()->format('m月d日'),
            'start_time' => '08:00',
            'end_time' => '17:00',
            'attendance_id' => $attendance->id,
            'request_date' => Carbon::now(),
            'correction_status_id' => AttendanceCorrection::PENDING,
            'reason' => '勤怠を間違えたため修正お願いします。',
        ])->assertRedirect('/attendance/list');

        // 修正リクエストが保存されたか確認
        AttendanceCorrection::where('attendance_id', $attendance->id)
            ->where('correction_status_id', AttendanceCorrection::PENDING)
            ->firstOrFail();

        // 申請一覧画面（承認待ちタブ）にアクセス
        $response = $this->get('/stamp_correction_request/list');
        $response->assertSee('詳細');

        // 申請詳細画面（勤怠詳細画面）にアクセス
        $response = $this->get('/attendance/' . $attendance->id);
        $response->assertStatus(200);
    }
}
