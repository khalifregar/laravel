<?php

namespace App\Http\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthService
{
    /**
     * Register new user with given role.
     */
    public function register(array $data, string $role = 'pembeli'): array
    {
        $user = User::create([
            'username' => $data['username'],
            'email' => $data['email'] ?? null,
            'phone' => $data['phone'] ?? null,
            'password' => isset($data['password']) ? Hash::make($data['password']) : null,
            'role' => $role,
        ]);

        $token = JWTAuth::fromUser($user);

        return [
            'token' => $token,
            'user' => $user,
        ];
    }

    /**
     * Attempt login and return token + user data.
     */
    public function login(array $data): array
    {
        $identifier = $data['identifier'];
        $password = $data['password'] ?? null;

        $user = User::where(function ($query) use ($identifier) {
            $query->where('email', $identifier)
                  ->orWhere('phone', $identifier)
                  ->orWhere('username', $identifier);
        })->first();

        if (!$user || !Hash::check($password, $user->password)) {
            throw ValidationException::withMessages([
                'identifier' => ['Login gagal. Email, nomor HP, atau username tidak ditemukan atau password salah.'],
            ]);
        }

        $user->last_login_at = now();
        $user->save();

        $token = JWTAuth::fromUser($user);

        return [
            'token' => $token,
            'user' => $user,
        ];
    }

    /**
     * Return current user from any valid guard.
     */
    public function me(): ?User
    {
        foreach (['api', 'admin', 'penjual', 'pembeli'] as $guard) {
            if (auth($guard)->check()) {
                return auth($guard)->user();
            }
        }

        return null;
    }

    /**
     * Logout from the current authenticated guard.
     */
    public function logout(): void
    {
        foreach (['api', 'admin', 'penjual', 'pembeli'] as $guard) {
            if (auth($guard)->check()) {
                auth($guard)->logout();
                return;
            }
        }
    }

    /**
     * Refresh token from the active guard.
     */
    public function refresh(): string
    {
        foreach (['api', 'admin', 'penjual', 'pembeli'] as $guard) {
            if (auth($guard)->check()) {
                return auth($guard)->refresh();
            }
        }

        throw ValidationException::withMessages([
            'token' => ['Token tidak valid atau tidak ditemukan.'],
        ]);
    }
}
