<?php

namespace Tests\Feature\Command;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class CreateAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_creates_an_admin_user(): void
    {
        $exitCode = Artisan::call('create:admin', [
            '--name' => 'Store Admin',
            '--email' => 'storeadmin@example.com',
            '--password' => 'secure-password',
        ]);

        $this->assertSame(0, $exitCode);

        $user = User::where('email', 'storeadmin@example.com')->first();

        $this->assertNotNull($user);
        $this->assertSame('admin', $user->role);
        $this->assertNotSame('secure-password', $user->password);
    }

    public function test_command_rejects_duplicate_email(): void
    {
        User::create([
            'name' => 'Existing User',
            'email' => 'taken@example.com',
            'password' => bcrypt('password'),
            'role' => 'pelanggan',
        ]);

        $exitCode = Artisan::call('create:admin', [
            '--name' => 'New Admin',
            '--email' => 'taken@example.com',
            '--password' => 'secure-password',
        ]);

        $this->assertSame(1, $exitCode);
        $this->assertSame(1, User::where('email', 'taken@example.com')->count());
    }

    public function test_command_rejects_weak_password(): void
    {
        $exitCode = Artisan::call('create:admin', [
            '--name' => 'Weak Password',
            '--email' => 'weak@example.com',
            '--password' => 'short',
        ]);

        $this->assertSame(1, $exitCode);
        $this->assertDatabaseMissing('users', ['email' => 'weak@example.com']);
    }

    public function test_command_validates_email_format(): void
    {
        $exitCode = Artisan::call('create:admin', [
            '--name' => 'Bad Email',
            '--email' => 'not-an-email',
            '--password' => 'secure-password',
        ]);

        $this->assertSame(1, $exitCode);
        $this->assertDatabaseMissing('users', ['email' => 'not-an-email']);
    }
}