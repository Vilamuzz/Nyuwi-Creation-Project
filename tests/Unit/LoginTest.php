<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Login;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    protected $loginService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->loginService = new Login();
    }

    public function test_user_can_login_with_valid_credentials()
    {
        // Membuat user dummy
        $user = User::factory()->create([
            'password' => bcrypt($password = 'password123')
        ]);

        // Menggunakan email sebagai field login sesuai dengan model User
        $credentials = ['email' => $user->email, 'password' => $password];
        $response = $this->loginService->authenticate($credentials);

        $this->assertTrue($response['status']);
        $this->assertEquals('Login berhasil', $response['message']);
    }

    public function test_user_cannot_login_with_invalid_credentials()
    {
        // Menggunakan email sebagai field login yang benar
        $credentials = ['email' => 'wrong_user', 'password' => 'wrong_password'];
        $response = $this->loginService->authenticate($credentials);

        $this->assertFalse($response['status']);
        $this->assertEquals('Login gagal, username atau password salah', $response['message']);
    }

    public function test_user_cannot_login_with_empty_credentials()
    {
        $credentials = ['email' => '', 'password' => ''];
        $response = $this->loginService->authenticate($credentials);

        $this->assertFalse($response['status']);
        $this->assertEquals('Username atau password kosong', $response['message']);
    }

    public function test_user_can_logout()
    {
        $response = $this->loginService->logout();
        $this->assertTrue($response['status']);
        $this->assertEquals('Logout berhasil', $response['message']);
    }
}
