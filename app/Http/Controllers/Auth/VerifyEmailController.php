<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;

class VerifyEmailController extends Controller
{
    /**
     * Mark the authenticated user's email address as verified.
     */
    public function __invoke(EmailVerificationRequest $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return $this->redirectBasedOnRole($request->user());
        }

        if ($request->user()->markEmailAsVerified()) {
            event(new Verified($request->user()));
        }

        return $this->redirectBasedOnRole($request->user())
            ->with('verified', 1)
            ->with('message', 'Email berhasil diverifikasi!')
            ->with('status', 'verification-success');
    }

    /**
     * Redirect user based on their role
     */
    protected function redirectBasedOnRole($user): RedirectResponse
    {
        if ($user->role === 'admin') {
            return redirect()->route('dashboard', ['verified' => 1]);
        }

        // Default for customers
        return redirect()->route('home', ['verified' => 1]);
    }
}
