<?php

namespace App;

use Illuminate\Support\Facades\Auth;

class Login
{
    public function authenticate($credentials)
    {
        // Periksa apakah email dan password ada
        if (empty($credentials['email']) || empty($credentials['password'])) {
            return ['status' => false, 'message' => 'Username atau password kosong'];
        }

        // Gunakan email untuk autentikasi
        if (Auth::attempt($credentials)) {
            return ['status' => true, 'message' => 'Login berhasil'];
        }

        return ['status' => false, 'message' => 'Login gagal, username atau password salah'];
    }

    public function logout()
    {
        Auth::logout();
        return ['status' => true, 'message' => 'Logout berhasil'];
    }
}
