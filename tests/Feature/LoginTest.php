<?php
use PHPUnit\Framework\TestCase;

class LoginService
{
    private $accounts = [
        'dias' => ['password' => 'dias123', 'role' => 'user', 'active' => true, 'failed_attempts' => 0, 'lock_time' => 0],
        'admin' => ['password' => 'adminpass', 'role' => 'admin', 'active' => true, 'failed_attempts' => 0, 'lock_time' => 0],
        'adi' => ['password' => 'adil22', 'role' => 'user', 'active' => false, 'failed_attempts' => 0, 'lock_time' => 0],
    ];

    const MAX_FAILED_ATTEMPTS = 3;  // Maksimal percobaan login gagal
    const LOCK_TIME = 120;  // Waktu kunci dalam detik

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
                $this->accounts[$username]['failed_attempts'] = 0;
                $this->accounts[$username]['lock_time'] = 0;
            }
        }

        if (!$account['active']) {
            return new LoginResult(false, 'Akun tidak aktif');
        }

        if ($account['password'] !== $password) {
            $this->accounts[$username]['failed_attempts']++;
            if ($this->accounts[$username]['failed_attempts'] >= self::MAX_FAILED_ATTEMPTS) {
                $this->accounts[$username]['lock_time'] = time();
                return new LoginResult(false, 'Akun terkunci. Silakan coba lagi setelah ' . self::LOCK_TIME . ' detik.');
            }
            return new LoginResult(false, 'Username atau password salah');
        }

        $this->accounts[$username]['failed_attempts'] = 0;
        $this->accounts[$username]['lock_time'] = 0;

        $redirectPage = $account['role'] === 'admin' ? 'admin_dashboard' : 'user_dashboard';
        return new LoginResult(true, '', $redirectPage);
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

class LoginTest extends TestCase
{
    private $loginService;

    protected function setUp(): void
    {
        $this->loginService = new LoginService();
    }

    public function testSuccessfulLoginUser()
    {
        $result = $this->loginService->login("dias", "dias123");
        $this->assertTrue($result->isSuccess());
        $this->assertEquals('user_dashboard', $result->getRedirectPage());
    }

    public function testEmptyUsername()
    {
        $result = $this->loginService->login("", "dias123");
        $this->assertFalse($result->isSuccess());
        $this->assertEquals('Username tidak boleh kosong', $result->getError());
    }

    public function testEmptyPassword()
    {
        $result = $this->loginService->login("dias", "");
        $this->assertFalse($result->isSuccess());
        $this->assertEquals('Password tidak boleh kosong', $result->getError());
    }

    public function testInactiveAccount()
    {
        $result = $this->loginService->login("adi", "adil22");
        $this->assertFalse($result->isSuccess());
        $this->assertEquals('Akun tidak aktif', $result->getError());
    }

    public function testBruteForceAttack()
    {
        $this->loginService->login("dias", "wrongpassword");
        $this->loginService->login("dias", "wrongpassword");
        $this->loginService->login("dias", "wrongpassword");

        $result = $this->loginService->login("dias", "dias123");
        $this->assertFalse($result->isSuccess());
        $this->assertStringContainsString('Akun terkunci. Silakan coba lagi setelah', $result->getError());
    }

    public function testSuccessfulLoginAdmin()
    {
        $result = $this->loginService->login("admin", "adminpass");
        $this->assertTrue($result->isSuccess());
        $this->assertEquals('admin_dashboard', $result->getRedirectPage());
    }

    public function testEmptyUsernameAdmin()
    {
        $result = $this->loginService->login("", "adminpass");
        $this->assertFalse($result->isSuccess());
        $this->assertEquals('Username tidak boleh kosong', $result->getError());
    }

    public function testWrongPasswordAdmin()
    {
        $result = $this->loginService->login("admin", "wrongpass");
        $this->assertFalse($result->isSuccess());
        $this->assertEquals('Username atau password salah', $result->getError());
    }

    public function testEmptyPasswordAdmin()
    {
        $result = $this->loginService->login("admin", "");
        $this->assertFalse($result->isSuccess());
        $this->assertEquals('Password tidak boleh kosong', $result->getError());
    }

    public function testBruteForceAttackAdmin()
    {
        $this->loginService->login("admin", "wrongpass");
        $this->loginService->login("admin", "wrongpass");
        $this->loginService->login("admin", "wrongpass");

        $result = $this->loginService->login("admin", "adminpass");
        $this->assertFalse($result->isSuccess());
        $this->assertStringContainsString('Akun terkunci. Silakan coba lagi setelah', $result->getError());
    }
}

