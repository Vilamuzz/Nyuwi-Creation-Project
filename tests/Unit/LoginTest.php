<?php

namespace Tests\Unit;

use App\Models\User;
use App\Login; // Assuming this is the Login service you’re using
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    protected $login;

    protected function setUp(): void
    {
        parent::setUp();
        $this->login = new Login();
    }

    public function testLoginWithValidCredentials()
    {
        $user = User::factory()->create([
            'email' => 'user@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->login->attempt(['email' => $user->email, 'password' => 'password123']);

        $this->assertTrue($response);
        $this->assertAuthenticatedAs($user);
    }

    public function testLoginWithInvalidCredentials()
    {
        User::factory()->create([
            'email' => 'user@example.com',
            'password' => Hash::make('password123'),
        ]);

        // Attempt login with incorrect password
        $response = $this->login->attempt(['email' => 'user@example.com', 'password' => 'wrongpassword']);

        $this->assertFalse($response);
        $this->assertGuest();
    }

    public function testLoginWithEmptyUsernameOrPassword()
    {
        $response = $this->login->attempt(['email' => '', 'password' => 'password123']);
        $this->assertFalse($response);

        $response = $this->login->attempt(['email' => 'user@example.com', 'password' => '']);
        $this->assertFalse($response);
    }

    public function testLoginWithInactiveAccount()
    {
        $user = User::factory()->create([
            'email' => 'inactive@example.com',
            'password' => Hash::make('password123'),
            'active' => false, // Assuming you have an `active` column for user status
        ]);

        $response = $this->login->attempt(['email' => $user->email, 'password' => 'password123']);

        $this->assertFalse($response);
        $this->assertGuest();
    }

    public function testBruteForceProtection()
    {
        User::factory()->create([
            'email' => 'user@example.com',
            'password' => Hash::make('password123'),
        ]);

        for ($i = 0; $i < 5; $i++) {
            $response = $this->login->attempt(['email' => 'user@example.com', 'password' => 'wrongpassword']);
            $this->assertFalse($response);
        }

        // Simulate lockout after multiple failed attempts (assuming your app handles this).
        $this->expectException(\Exception::class);
        $this->login->attempt(['email' => 'user@example.com', 'password' => 'password123']);
    }
}
