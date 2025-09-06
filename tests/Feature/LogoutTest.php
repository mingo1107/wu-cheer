<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class LogoutTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 測試登出功能
     */
    public function test_user_can_logout_successfully(): void
    {
        // 建立測試使用者
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'email_verified_at' => now(),
        ]);

        // 登入使用者
        $this->actingAs($user);

        // 確認使用者已登入
        $this->assertTrue(Auth::check());

        // 呼叫登出 API
        $response = $this->postJson('/api/account/logout');

        // 確認回應成功
        $response->assertStatus(200)
                ->assertJson([
                    'status' => true,
                    'message' => '登出成功'
                ]);

        // 確認使用者已登出
        $this->assertFalse(Auth::check());
    }

    /**
     * 測試未登入使用者無法登出
     */
    public function test_unauthenticated_user_cannot_logout(): void
    {
        // 確認使用者未登入
        $this->assertFalse(Auth::check());

        // 呼叫登出 API
        $response = $this->postJson('/api/account/logout');

        // 確認回應 401 未授權
        $response->assertStatus(401)
                ->assertJson([
                    'status' => false,
                    'message' => '使用者未登入'
                ]);
    }

    /**
     * 測試登出後 session 被清除
     */
    public function test_logout_clears_session(): void
    {
        // 建立測試使用者
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'email_verified_at' => now(),
        ]);

        // 登入使用者
        $this->actingAs($user);

        // 確認 session 存在
        $this->assertNotNull(session()->getId());

        // 呼叫登出 API
        $response = $this->postJson('/api/account/logout');

        // 確認回應成功
        $response->assertStatus(200);

        // 確認使用者已登出
        $this->assertFalse(Auth::check());
    }
}
