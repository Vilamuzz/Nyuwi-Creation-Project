namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function it_can_login_with_valid_credentials()
    {
        // Membuat user baru
        $user = User::factory()->create([
            'password' => Hash::make('password123') // Pastikan password ter-enkripsi
        ]);

        // Melakukan POST request ke route login
        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        // Memastikan respons sukses
        $response->assertStatus(200)
                 ->assertJson(['message' => 'Login successful']);
    }

    public function it_cannot_login_with_invalid_credentials()
    {
        // Membuat user baru
        $user = User::factory()->create([
            'password' => Hash::make('password123') // Pastikan password ter-enkripsi
        ]);

        // Melakukan POST request dengan email dan password yang salah
        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'wrongpassword', // Password yang salah
        ]);

        // Memastikan respons gagal
        $response->assertStatus(401)
                 ->assertJson(['message' => 'Unauthorized']);
    }
}
