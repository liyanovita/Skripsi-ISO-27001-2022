<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Exception;

class SocialAuthController extends Controller
{
    /**
     * Redirect to OAuth provider
     */
    public function redirect(string $provider)
    {
        $this->validateProvider($provider);
        
        return Socialite::driver($provider)->redirect();
    }

    /**
     * Handle OAuth callback
     */
    public function callback(string $provider)
    {
        $this->validateProvider($provider);

        try {
            $socialUser = Socialite::driver($provider)->user();
            
            // Find or create user
            $user = $this->findOrCreateUser($socialUser, $provider);
            
            // Login user
            Auth::login($user, true);

            // Block suspended users — logout immediately
            if ($user->status === 'suspended') {
                Auth::logout();
                return redirect()->route('login')
                    ->withErrors(['email' => 'Akun Anda telah ditangguhkan. Hubungi administrator untuk informasi lebih lanjut.']);
            }

            // Admin langsung ke admin panel, user biasa ke dashboard
            $destination = $user->role === 'admin'
                ? route('admin.dashboard')
                : route('dashboard');

            return redirect($destination)
                ->with('status', __('Successfully logged in with :provider!', ['provider' => ucfirst($provider)]));
                
        } catch (Exception $e) {
            return redirect()->route('login')
                ->withErrors(['oauth' => __('Unable to login with :provider. Please try again.', ['provider' => ucfirst($provider)])]);
        }
    }

    /**
     * Find or create user from social provider
     */
    protected function findOrCreateUser($socialUser, string $provider): User
    {
        // Check if user exists with this provider
        $user = User::where('provider', $provider)
            ->where('provider_id', $socialUser->getId())
            ->first();

        if ($user) {
            // Update avatar if changed
            if ($socialUser->getAvatar() && $user->avatar !== $socialUser->getAvatar()) {
                $user->update(['avatar' => $socialUser->getAvatar()]);
            }
            return $user;
        }

        // Check if user exists with this email
        $existingUser = User::where('email', $socialUser->getEmail())->first();

        if ($existingUser) {
            // Link OAuth account to existing user
            $existingUser->update([
                'provider' => $provider,
                'provider_id' => $socialUser->getId(),
                'avatar' => $socialUser->getAvatar(),
            ]);
            return $existingUser;
        }

        // Create new user
        return User::create([
            'name' => $socialUser->getName() ?? $socialUser->getNickname() ?? 'User',
            'email' => $socialUser->getEmail(),
            'provider' => $provider,
            'provider_id' => $socialUser->getId(),
            'avatar' => $socialUser->getAvatar(),
            'password' => Hash::make(Str::random(24)), // Random password for OAuth users
            'email_verified_at' => now(), // OAuth emails are pre-verified
        ]);
    }

    /**
     * Validate OAuth provider
     */
    protected function validateProvider(string $provider): void
    {
        if (!in_array($provider, ['github', 'google'])) {
            abort(404, 'Invalid OAuth provider');
        }
    }
}
