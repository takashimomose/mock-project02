<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class GetDatetimeTest extends TestCase
{
    use DatabaseTransactions;

    public function test_get_date_on_attendance_page()
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

        // 現在の日付と時刻を生成
        $currentDate = now()->isoFormat('Y年M月D日') . '(' . ['日', '月', '火', '水', '木', '金', '土'][now()->dayOfWeek] . ')';
        $currentTime = now()->format('H:i');

        // ページに現在の日付が含まれていることを確認
        $response->assertSee($currentDate, false);
        $response->assertSee($currentTime, false);
    }
}
