<?php
use PHPUnit\Framework\TestCase;

class LoginTest extends TestCase
{
    private $loginService;

    protected function setUp(): void
    {
        $this->loginService = new LoginService();
    }

    // Test successful login with valid credentials
    public function testSuccessfulLogin()
    {
        $result = $this->loginService->login("dias", "dias123");
        $this->assertTrue($result->isSuccess());
    }

    // Test login with empty username
    public function testEmptyUsername()
    {
        $result = $this->loginService->login("", "dias123");
        $this->assertFalse($result->isSuccess());
        $this->assertEquals('Username tidak boleh kosong', $result->getError());
    }

    // Test login with empty password
    public function testEmptyPassword()
    {
        $result = $this->loginService->login("dias", "");
        $this->assertFalse($result->isSuccess());
        $this->assertEquals('Password tidak boleh kosong', $result->getError());
    }

    // Test login with invalid credentials
    public function testInvalidCredentials()
    {
        $result = $this->loginService->login("udin", "dindin22");
        $this->assertFalse($result->isSuccess());
        $this->assertEquals('Username atau password salah', $result->getError());
    }

    // Test login with inactive account
    public function testInactiveAccount()
    {
    $result = $this->loginService->login("adi", "adil22");
    $this->assertFalse($result->isSuccess());
    $this->assertEquals('Akun tidak aktif', $result->getError());
    }

    // Test login dengan terlalu banyak percobaan gagal (Brute Force)
    public function testBruteForceAttack()
    {
        // Melakukan 3 kali percobaan login dengan password salah
        $this->loginService->login("dias", "wrongpassword");
        $this->loginService->login("dias", "wrongpassword");
        $this->loginService->login("dias", "wrongpassword");

        // Setelah 3 percobaan gagal, akun harus terkunci
        $result = $this->loginService->login("dias", "dias123");
        $this->assertFalse($result->isSuccess());
        $this->assertStringContainsString('Akun terkunci. Silakan coba lagi setelah', $result->getError());
    }

}

class LoginService
{
    private $accounts = [
        'dias' => ['password' => 'dias123', 'active' => true, 'failed_attempts' => 0, 'lock_time' => 0],
        'adi' => ['password' => 'adil22', 'active' => false, 'failed_attempts' => 0, 'lock_time' => 0],
    ];    

    const MAX_FAILED_ATTEMPTS = 3;  // Maksimal percobaan login gagal
    const LOCK_TIME = 120;  // Waktu kunci dalam detik (misalnya 2 menit)

    public function login($username, $password)
    {
        if (empty($username)) {
            return new LoginResult(false, 'Username tidak boleh kosong');
        }

        if (empty($password)) {
            return new LoginResult(false, 'Password tidak boleh kosong');
        }

        if (!isset($this->accounts[$username])) {
            return new LoginResult(false, 'Username atau password salah');
        }

        $account = $this->accounts[$username];

        // Cek apakah akun diblokir (karena terlalu banyak percobaan gagal)
        if ($account['failed_attempts'] >= self::MAX_FAILED_ATTEMPTS) {
            $time_left = time() - $account['lock_time'];
            if ($time_left < self::LOCK_TIME) {
                $remaining_time = self::LOCK_TIME - $time_left;
                return new LoginResult(false, 'Akun terkunci. Silakan coba lagi setelah ' . $remaining_time . ' detik.');
            } else {
                // Reset setelah waktu kunci berakhir
                $this->accounts[$username]['failed_attempts'] = 0;
                $this->accounts[$username]['lock_time'] = 0;
            }
        }

        if (!$account['active']) {
            return new LoginResult(false, 'Akun tidak aktif');
        }

        if ($account['password'] !== $password) {
            // Jika password salah, tingkatkan jumlah percobaan gagal
            $this->accounts[$username]['failed_attempts']++;
            if ($this->accounts[$username]['failed_attempts'] >= self::MAX_FAILED_ATTEMPTS) {
                // Kunci akun setelah jumlah percobaan gagal mencapai batas
                $this->accounts[$username]['lock_time'] = time();
                return new LoginResult(false, 'Akun terkunci. Silakan coba lagi setelah ' . self::LOCK_TIME . ' detik.');
            }
            return new LoginResult(false, 'Username atau password salah');
        }

        // Reset percobaan gagal dan lock time setelah login sukses
        $this->accounts[$username]['failed_attempts'] = 0;
        $this->accounts[$username]['lock_time'] = 0; // Reset lock time jika login sukses

        return new LoginResult(true, '', 'dashboard');
    }
}


class LoginResult
{
    private $success;
    private $error;
    private $redirectPage;

    public function __construct($success, $error = '', $redirectPage = '')
    {
        $this->success = $success;
        $this->error = $error;
        $this->redirectPage = $redirectPage;
    }

    public function isSuccess()
    {
        return $this->success;
    }

    public function getError()
    {
        return $this->error;
    }

    public function getRedirectPage()
    {
        return $this->redirectPage;
    }
}
