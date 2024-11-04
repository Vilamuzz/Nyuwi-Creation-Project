<?php

namespace App;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class Login
{
    public function login($email, $password)
    {
        // Validasi email dan password
        if (strlen($email) < 5 || strlen($password) < 5) {
            throw ValidationException::withMessages(['error' => 'Email atau password terlalu pendek.']);
        }

        // Cari user berdasarkan email
        $user = User::where('email', $email)->first();

        // Jika user ditemukan dan password cocok, buat sesi login
        if ($user && Hash::check($password, $user->password)) {
            Auth::login($user);
            return true;
        }

        // Jika gagal, lemparkan exception
        throw ValidationException::withMessages(['error' => 'Login gagal. Email atau password salah.']);
    }

    public function logout()
    {
        Auth::logout();
    }

    public function isAuthenticated()
    {
        return Auth::check();
    }
}
