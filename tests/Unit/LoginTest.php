<?php

namespace Tests\Unit;

use App\Models\User;
use App\Login;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
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
        // Buat user dengan password terenkripsi
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('validPassword'),
        ]);

        // Uji login dengan kredensial yang valid
        $result = $this->login->login('test@example.com', 'validPassword');

        $this->assertTrue($result);
        $this->assertTrue(Auth::check());
        $this->assertEquals(Auth::user()->id, $user->id);
    }

    public function testLoginWithInvalidCredentials()
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Login gagal. Email atau password salah.');

        // Buat user dengan password terenkripsi
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('validPassword'),
        ]);

        // Uji login dengan password yang salah
        $this->login->login('test@example.com', 'invalidPassword');
    }

    public function testLoginWithShortCredentials()
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Email atau password terlalu pendek.');

        // Uji login dengan email dan password yang pendek
        $this->login->login('test', 'pass');
    }

    public function testLogout()
    {
        // Buat user dan login
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('validPassword'),
        ]);
        $this->login->login('test@example.com', 'validPassword');

        // Uji logout
        $this->login->logout();
        $this->assertFalse(Auth::check());
    }

    public function testIsAuthenticated()
    {
        // Buat user dan login
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('validPassword'),
        ]);
        $this->login->login('test@example.com', 'validPassword');

        $this->assertTrue($this->login->isAuthenticated());

        // Logout dan periksa status autentikasi
        $this->login->logout();
        $this->assertFalse($this->login->isAuthenticated());
    }
}
