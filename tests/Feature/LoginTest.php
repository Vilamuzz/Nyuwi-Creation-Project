<?php
use PHPUnit\Framework\TestCase;

class LoginTest extends TestCase
{
    private $loginService;
    private $maxLoginAttempts = 3;
    private $lockoutTime = 120; // 2 minutes in seconds

    protected function setUp(): void
    {
        // Initialize login service - adjust based on your actual implementation
        $this->loginService = new LoginService();
    }

    /**
     * Test successful login with valid credentials
     */
    public function testSuccessfulLogin()
    {
        $username = "validuser";
        $password = "validpass123";
        
        $result = $this->loginService->login($username, $password);
        
        $this->assertTrue($result->isSuccess());
        $this->assertEquals('dashboard', $result->getRedirectPage());
        $this->assertNotEmpty($result->getSessionToken());
    }

    /**
     * Test login with empty username
     */
    public function testEmptyUsername()
    {
        $username = "";
        $password = "somepassword";
        
        $result = $this->loginService->login($username, $password);
        
        $this->assertFalse($result->isSuccess());
        $this->assertEquals('Username tidak boleh kosong', $result->getError());
    }

    /**
     * Test login with empty password
     */
    public function testEmptyPassword()
    {
        $username = "someuser";
        $password = "";
        
        $result = $this->loginService->login($username, $password);
        
        $this->assertFalse($result->isSuccess());
        $this->assertEquals('Password tidak boleh kosong', $result->getError());
    }

    /**
     * Test login with invalid credentials
     */
    public function testInvalidCredentials()
    {
        $username = "wronguser";
        $password = "wrongpass";
        
        $result = $this->loginService->login($username, $password);
        
        $this->assertFalse($result->isSuccess());
        $this->assertEquals('Username atau password salah', $result->getError());
    }

    /**
     * Test login with inactive account
     */
    public function testInactiveAccount()
    {
        $username = "inactiveuser";
        $password = "somepass123";
        
        $result = $this->loginService->login($username, $password);
        
        $this->assertFalse($result->isSuccess());
        $this->assertEquals('Akun tidak aktif', $result->getError());
    }

    /**
     * Test brute force protection
     */
    public function testBruteForceProtection()
    {
        $username = "testuser";
        $password = "wrongpass";
        
        // Simulate multiple failed login attempts
        for ($i = 0; $i < $this->maxLoginAttempts; $i++) {
            $result = $this->loginService->login($username, $password);
            $this->assertFalse($result->isSuccess());
        }
        
        // Try one more time after max attempts
        $result = $this->loginService->login($username, $password);
        
        $this->assertFalse($result->isSuccess());
        $this->assertEquals(
            'Terlalu banyak percobaan login. Silakan coba lagi dalam 15 menit',
            $result->getError()
        );
        
        // Verify account is locked
        $this->assertTrue($this->loginService->isAccountLocked($username));
        
        // Verify correct lockout duration
        $this->assertEquals(
            $this->lockoutTime,
            $this->loginService->getRemainingLockoutTime($username)
        );
    }

    /**
     * Test login after lockout period expires
     */
    public function testLoginAfterLockoutExpires()
    {
        $username = "testuser";
        $password = "correctpass123";
        
        // Simulate account being locked
        $this->loginService->lockAccount($username);
        
        // Fast forward past lockout period
        $this->loginService->setCurrentTime(time() + $this->lockoutTime + 1);
        
        // Try logging in with correct credentials
        $result = $this->loginService->login($username, $password);
        
        $this->assertTrue($result->isSuccess());
        $this->assertFalse($this->loginService->isAccountLocked($username));
    }
}

// Mock classes to represent the system - replace with your actual implementation

class LoginService
{
    private $accounts = [];
    private $loginAttempts = [];
    private $lockedAccounts = [];
    private $currentTime;

    public function __construct()
    {
        $this->currentTime = time();
        // Mock user data
        $this->accounts = [
            'validuser' => [
                'password' => 'validpass123',
                'active' => true
            ],
            'inactiveuser' => [
                'password' => 'somepass123',
                'active' => false
            ],
        ];
    }

    public function login($username, $password)
    {
        if (empty($username)) {
            return new LoginResult(false, 'Username tidak boleh kosong');
        }

        if (empty($password)) {
            return new LoginResult(false, 'Password tidak boleh kosong');
        }

        if ($this->isAccountLocked($username)) {
            $remainingTime = $this->getRemainingLockoutTime($username);
            return new LoginResult(
                false,
                'Terlalu banyak percobaan login. Silakan coba lagi dalam 15 menit'
            );
        }

        if (!isset($this->accounts[$username])) {
            $this->incrementLoginAttempts($username);
            return new LoginResult(false, 'Username atau password salah');
        }

        $account = $this->accounts[$username];

        if (!$account['active']) {
            return new LoginResult(false, 'Akun tidak aktif');
        }

        if ($account['password'] !== $password) {
            $this->incrementLoginAttempts($username);
            return new LoginResult(false, 'Username atau password salah');
        }

        return new LoginResult(true, '', 'dashboard', 'mock_session_token');
    }

    public function isAccountLocked($username)
    {
        if (!isset($this->lockedAccounts[$username])) {
            return false;
        }
        return $this->lockedAccounts[$username] > $this->currentTime;
    }

    public function getRemainingLockoutTime($username)
    {
        if (!$this->isAccountLocked($username)) {
            return 0;
        }
        return $this->lockedAccounts[$username] - $this->currentTime;
    }

    public function lockAccount($username)
    {
        $this->lockedAccounts[$username] = $this->currentTime + 900;
    }

    public function setCurrentTime($time)
    {
        $this->currentTime = $time;
    }

    private function incrementLoginAttempts($username)
    {
        if (!isset($this->loginAttempts[$username])) {
            $this->loginAttempts[$username] = 0;
        }
        $this->loginAttempts[$username]++;

        if ($this->loginAttempts[$username] >= 3) {
            $this->lockAccount($username);
        }
    }
}

class LoginResult
{
    private $success;
    private $error;
    private $redirectPage;
    private $sessionToken;

    public function __construct($success, $error = '', $redirectPage = '', $sessionToken = '')
    {
        $this->success = $success;
        $this->error = $error;
        $this->redirectPage = $redirectPage;
        $this->sessionToken = $sessionToken;
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

    public function getSessionToken()
    {
        return $this->sessionToken;
    }
}