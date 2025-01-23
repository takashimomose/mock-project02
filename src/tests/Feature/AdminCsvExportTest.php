<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminCsvExportTest extends TestCase
{
    use DatabaseTransactions;

    private function createUser()
    {
        return User::create([
            'role_id' => User::ROLE_GENERAL,
            'name' => 'テストユーザー',
            'email' => 'registered01@example.com',
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

    public function test_export_csv_for_current_month()
    {
        $user = $this->createUser();

        $adminUser = $this->createAdminUser();

        $currentMonth = Carbon::now()->format('Y-m');

        $attendance = $this->createAttendance($user->id);

        $breakTime = $this->createBreakTime($attendance->id);

        $response = $this->get('/admin/login');
        $response->assertStatus(200);

        $response = $this->post('/admin/login', [
            'email' => 'admin@example.com',
            'password' => 'adminpassword123',
        ]);

        $this->assertAuthenticatedAs($adminUser);

        $response = $this->get('/admin/attendance/staff/' . $user->id);

        $response->assertStatus(200);
        $response->assertSee('CSV出力');

        // CSVエクスポートのリクエストを実行
        $response = $this->get(route('admin.staff.export', ['id' => $user->id]));

        // レスポンスヘッダーを確認
        $fileName = sprintf('%s_%s_勤怠情報.csv', $user->name, $currentMonth);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $response->assertHeader('Content-Disposition', 'attachment; filename=' . $fileName);

        // CSVデータをストリームから取得
        ob_start();
        $response->sendContent();
        $csvContent = ob_get_clean();

        // CSVデータが空でないことを確認
        $this->assertNotEmpty($csvContent);

        // BOMを削除
        $csvContent = preg_replace('/^\xEF\xBB\xBF/', '', $csvContent);

        // CSVを行ごとに分割
        $lines = explode("\n", trim($csvContent));

        // ヘッダー行を検証
        $expectedHeader = ['日付', '出勤', '退勤', '休憩', '合計'];
        $csvHeader = str_getcsv($lines[0]);
        $this->assertEquals($expectedHeader, $csvHeader);

        // データ行を検証
        $expectedData = [
            $attendance->date,
            Carbon::parse($attendance->start_time)->format('H:i'),
            Carbon::parse($attendance->end_time)->format('H:i'),
            Carbon::parse('00:00')->addMinutes($breakTime->break_time)->format('H:i'),
            Carbon::parse('00:00')->addMinutes($attendance->working_hours)->format('H:i'),
        ];
        $csvData = str_getcsv($lines[1]);
        $this->assertEquals($expectedData, $csvData);
    }

    public function test_export_csv_for_previous_month()
    {
        $user = $this->createUser();

        $adminUser = $this->createAdminUser();

        $currentMonth = Carbon::now();
        $previousMonth = $currentMonth->copy()->subMonth();
        $previousMonthFormatted = $previousMonth->format('Y-m');

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

        $response = $this->get('/admin/login');
        $response->assertStatus(200);

        $response = $this->post('/admin/login', [
            'email' => 'admin@example.com',
            'password' => 'adminpassword123',
        ]);

        $this->assertAuthenticatedAs($adminUser);

        $response = $this->get('/admin/attendance/staff/' . $user->id . '?month=' . $previousMonthFormatted);

        $response->assertStatus(200);
        $response->assertSee('CSV出力');

        // CSVエクスポートのリクエストを実行
        $response = $this->get(route('admin.staff.export', [
            'id' => $user->id
        ]) . '?month=' . $previousMonthFormatted);

        // レスポンスヘッダーを確認
        $fileName = sprintf('%s_%s_勤怠情報.csv', $user->name, $previousMonthFormatted);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $response->assertHeader('Content-Disposition', 'attachment; filename=' . $fileName);

        // CSVデータをストリームから取得
        ob_start();
        $response->sendContent();
        $csvContent = ob_get_clean();

        // CSVデータが空でないことを確認
        $this->assertNotEmpty($csvContent);

        // BOMを削除
        $csvContent = preg_replace('/^\xEF\xBB\xBF/', '', $csvContent);

        // CSVを行ごとに分割
        $lines = explode("\n", trim($csvContent));

        // ヘッダー行を検証
        $expectedHeader = ['日付', '出勤', '退勤', '休憩', '合計'];
        $csvHeader = str_getcsv($lines[0]);
        $this->assertEquals($expectedHeader, $csvHeader);

        // データ行を検証
        $expectedData = [
            $attendance->date,
            Carbon::parse($attendance->start_time)->format('H:i'),
            Carbon::parse($attendance->end_time)->format('H:i'),
            Carbon::parse('00:00')->addMinutes($breakTime->break_time)->format('H:i'),
            Carbon::parse('00:00')->addMinutes($attendance->working_hours)->format('H:i'),
        ];
        $csvData = str_getcsv($lines[1]);
        $this->assertEquals($expectedData, $csvData);
    }

    public function test_export_csv_for_next_month()
    {
        $user = $this->createUser();

        $adminUser = $this->createAdminUser();

        $currentMonth = Carbon::now();
        $nextMonth = $currentMonth->copy()->addMonth();
        $nextMonthFormatted = $nextMonth->format('Y-m');

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

        $response = $this->get('/admin/login');
        $response->assertStatus(200);

        $response = $this->post('/admin/login', [
            'email' => 'admin@example.com',
            'password' => 'adminpassword123',
        ]);

        $this->assertAuthenticatedAs($adminUser);

        $response = $this->get('/admin/attendance/staff/' . $user->id . '?month=' . $nextMonthFormatted);

        $response->assertStatus(200);
        $response->assertSee('CSV出力');

        // CSVエクスポートのリクエストを実行
        $response = $this->get(route('admin.staff.export', [
            'id' => $user->id
        ]) . '?month=' . $nextMonthFormatted);

        // レスポンスヘッダーを確認
        $fileName = sprintf('%s_%s_勤怠情報.csv', $user->name, $nextMonthFormatted);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $response->assertHeader('Content-Disposition', 'attachment; filename=' . $fileName);

        // CSVデータをストリームから取得
        ob_start();
        $response->sendContent();
        $csvContent = ob_get_clean();

        // CSVデータが空でないことを確認
        $this->assertNotEmpty($csvContent);

        // BOMを削除
        $csvContent = preg_replace('/^\xEF\xBB\xBF/', '', $csvContent);

        // CSVを行ごとに分割
        $lines = explode("\n", trim($csvContent));

        // ヘッダー行を検証
        $expectedHeader = ['日付', '出勤', '退勤', '休憩', '合計'];
        $csvHeader = str_getcsv($lines[0]);
        $this->assertEquals($expectedHeader, $csvHeader);

        // データ行を検証
        $expectedData = [
            $attendance->date,
            Carbon::parse($attendance->start_time)->format('H:i'),
            Carbon::parse($attendance->end_time)->format('H:i'),
            Carbon::parse('00:00')->addMinutes($breakTime->break_time)->format('H:i'),
            Carbon::parse('00:00')->addMinutes($attendance->working_hours)->format('H:i'),
        ];
        $csvData = str_getcsv($lines[1]);
        $this->assertEquals($expectedData, $csvData);
    }
}
