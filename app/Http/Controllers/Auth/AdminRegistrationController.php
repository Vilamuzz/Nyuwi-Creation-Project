<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\AdminRegisterRequest;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Inertia\Inertia;
use Inertia\Response;

class AdminRegistrationController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('Auth/AdminRegister');
    }

    public function store(AdminRegisterRequest $request): RedirectResponse
    {
        $request->ensureIsNotRateLimited();

        $user = User::create([
            'name' => $request->validated('name'),
            'email' => $request->validated('email'),
            'password' => Hash::make($request->validated('password')),
            'role' => 'admin'
        ]);

        event(new Registered($user));

        Auth::login($user);

        RateLimiter::clear($request->throttleKey());

        return redirect()->route('dashboard');
    }
}
