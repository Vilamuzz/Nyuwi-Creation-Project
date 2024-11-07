<?php

namespace App;

use Illuminate\Support\Facades\Auth;
use App\Models\User;

class Login
{
    public function authenticate($credentials)
    {
        if (empty($credentials['email']) || empty($credentials['password'])) {
            return ['status' => false, 'message' => 'Username atau password kosong'];
        }

        if (Auth::attempt($credentials)) {
            $user = Auth::user();

            // Cek role pengguna
            if ($this->isAdmin($user)) {
                return ['status' => true, 'message' => 'Login berhasil sebagai admin'];
            } elseif ($this->isPelanggan($user)) {
                return ['status' => true, 'message' => 'Login berhasil sebagai pelanggan'];
            }

            return ['status' => true, 'message' => 'Login berhasil, tetapi role tidak dikenali'];
        }

        return ['status' => false, 'message' => 'Login gagal, username atau password salah'];
    }

    public function logout()
    {
        Auth::logout();
        return ['status' => true, 'message' => 'Logout berhasil'];
    }

    protected function isAdmin(?User $user): bool
    {
        return $user && $user->role === 'admin';
    }

    protected function isPelanggan(?User $user): bool
    {
        return $user && $user->role === 'pelanggan';
    }
}
