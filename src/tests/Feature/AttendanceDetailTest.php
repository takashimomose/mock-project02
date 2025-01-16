<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AttendanceDetailTest extends TestCase
{
    use DatabaseTransactions;

    const TEST_USER_NAME = 'テストユーザー';

    public function test_name()
    {
        // ログインのユーザーを作成
        $user = User::create([
            'role_id' => User::ROLE_GENERAL,
            'name' => self::TEST_USER_NAME,
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
        $response->assertSee(self::TEST_USER_NAME);
    }

    public function test_date()
    {
        // ログインのユーザーを作成
        $user = User::create([
            'role_id' => User::ROLE_GENERAL,
            'name' => self::TEST_USER_NAME,
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

        $data['date_year'] = Carbon::parse($attendance->date)->format('Y年');
        $data['date_day'] = Carbon::parse($attendance->date)->format('m月d日');

        $response = $this->get('/attendance/list');
        $response->assertSee('詳細');

        $response = $this->get('/attendance/' . $attendance->id);
        $response->assertSee($data['date_year']);
        $response->assertSee($data['date_day']);
    }

    public function test_start_time_and_end_time()
    {
        // ログインのユーザーを作成
        $user = User::create([
            'role_id' => User::ROLE_GENERAL,
            'name' => self::TEST_USER_NAME,
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
        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }

    public function test_break_start_time_and_end_time()
    {
        // ログインのユーザーを作成
        $user = User::create([
            'role_id' => User::ROLE_GENERAL,
            'name' => self::TEST_USER_NAME,
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

        // 休憩データを作成
        BreakTime::create([
            'attendance_id' => $attendance->id,
            'start_time' => '12:00:00',
            'end_time' => '13:00:00',
        ]);

        $response = $this->get('/attendance/list');
        $response->assertSee('詳細');

        $response = $this->get('/attendance/' . $attendance->id);
        $response->assertSee('12:00');
        $response->assertSee('13:00');
    }
}
