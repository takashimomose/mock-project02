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

    public function test_export_csv()
    {
        // テスト用のユーザーを作成
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

        // テスト用の勤怠データを作成
        $currentMonth = Carbon::now()->format('Y-m');

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::now()->toDateString(),
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
            'working_hours' => 540,
            'attendance_status_id' => Attendance::STATUS_FINISHED,
        ]);

        $breakTime = BreakTime::create([
            'attendance_id' => $attendance->id,
            'start_time' => '12:00:00',
            'end_time' => '13:00:00',
            'break_time' => 60,
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

        // リクエストの実行
        $response = $this->get('/admin/attendance/staff/' . $user->id);

        // レスポンスのステータスコードを確認
        $response->assertStatus(200);
        $response->assertSee('CSV出力');

        // CSVエクスポートのリクエストを実行
        $response = $this->get(route('admin.staff.export', ['id' => $user->id]));

        // レスポンスヘッダーを確認
        $fileName = sprintf('%s_%s_勤怠情報.csv', $user->name, $currentMonth);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $response->assertHeader('Content-Disposition', 'attachment; filename=' . $fileName);

        // CSVデータを取得
        $output = $response->getContent();

        // BOMを削除 (UTF-8 BOMは "\xEF\xBB\xBF")
        $outputWithoutBom = preg_replace('/^\xEF\xBB\xBF/', '', $output);

        // CSV内容を確認
        $lines = explode("\n", trim($outputWithoutBom));
        $this->assertCount(2, $lines); // ヘッダー + 1データ

        // ヘッダー確認
        $expectedHeader = ['日付', '出勤', '退勤', '休憩', '合計'];
        $this->assertEquals($expectedHeader, str_getcsv($lines[0]));

        // データ確認
        $expectedRow = [
            $attendance->date,
            Carbon::parse($attendance->start_time)->format('H:i'),
            Carbon::parse($attendance->end_time)->format('H:i'),
            Carbon::parse('00:00')->addMinutes($breakTime->break_time)->format('H:i'),
            Carbon::parse('00:00')->addMinutes($attendance->working_hours)->format('H:i'),
        ];
        $this->assertEquals($expectedRow, str_getcsv($lines[1]));
    }
}
