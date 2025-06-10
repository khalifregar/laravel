<?php

namespace App\Http\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\JWTGuard;

class AuthService
{
    /**
     * Register user berdasarkan role.
     */
    public function register(array $data, string $role): array
    {
        $user = User::create([
            'username' => $data['username'],
            'email'    => $data['email'] ?? null,
            'phone'    => $data['phone'] ?? null,
            'password' => isset($data['password']) ? Hash::make($data['password']) : null,
            'role'     => $role,
        ]);

        $token = JWTAuth::fromUser($user);

        return [
            'token' => $token,
            'user'  => $user,
        ];
    }

    /**
     * Login user berdasarkan identifier (email, username, atau phone) dan role yang sesuai.
     */
    public function login(array $data, string $role): array
    {
        $identifier = $data['identifier'];
        $password   = $data['password'] ?? null;

        $user = User::where(function ($query) use ($identifier) {
                        $query->where('email', $identifier)
                              ->orWhere('phone', $identifier)
                              ->orWhere('username', $identifier);
                    })
                    ->where('role', $role)
                    ->first();

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
            'user'  => $user,
        ];
    }

    /**
     * Mengambil user dari token yang sedang aktif.
     */
    public function me(): ?User
    {
        /** @var JWTGuard $guard */
        $guard = auth('api');
        return $guard->user();
    }

    /**
     * Logout JWT user.
     */
    public function logout(): void
    {
        /** @var JWTGuard $guard */
        $guard = auth('api');
        $guard->logout();
    }

    /**
     * Refresh JWT token.
     */
    public function refresh(): string
    {
        /** @var JWTGuard $guard */
        $guard = auth('api');
        return $guard->refresh();
    }
}
