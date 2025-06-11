<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Services\AuthService;
use App\Http\Services\OtpService;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\JsonResponse;
use App\Models\WhatsappOtp;

class AuthController extends Controller
{
    protected AuthService $auth;
    protected OtpService $otp;

    public function __construct(AuthService $auth, OtpService $otp)
    {
        $this->auth = $auth;
        $this->otp = $otp;
    }

    public function registerAdmin(Request $request): JsonResponse
    {
        return $this->handleRegister($request, 'admin');
    }

    public function registerPenjual(Request $request): JsonResponse
    {
        return $this->handleRegister($request, 'penjual');
    }

    public function registerPembeli(Request $request): JsonResponse
    {
        return $this->handleRegister($request, 'pembeli');
    }

    public function login(Request $request): JsonResponse
    {
        return $this->handleLogin($request);
    }

    public function me(): JsonResponse
    {
        try {
            $user = $this->auth->me();

            if (!$user) {
                return response()->json([
                    'message' => 'Token tidak valid atau user tidak ditemukan.'
                ], 401);
            }

            return response()->json([
                'message' => 'Berhasil mengambil data pengguna.',
                'data' => [
                    'id' => $user->id,
                    'username' => $user->username,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'role' => $user->role,
                    'created_at' => $user->created_at,
                ]
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan saat mengambil data pengguna.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function updateProfile(Request $request, $id): JsonResponse
    {
        $user = $this->auth->me();

        if ($user->id != $id) {
            return response()->json([
                'message' => 'Akses ditolak. ID tidak cocok dengan token.'
            ], 403);
        }

        $data = $request->validate([
            'username' => 'sometimes|string|unique:users,username,' . $user->id,
            'email' => 'sometimes|email|unique:users,email,' . $user->id,
            'phone' => [
                'sometimes',
                'regex:/^62[0-9]{9,13}$/',
                'unique:users,phone,' . $user->id
            ],
        ]);

        $user->update($data);

        return response()->json([
            'message' => 'Profil berhasil diperbarui.',
            'data' => [
                'id' => $user->id,
                'username' => $user->username,
                'email' => $user->email,
                'phone' => $user->phone,
                'role' => $user->role,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
            ]
        ]);
    }

    public function logout($id): JsonResponse
    {
        $user = $this->auth->me();

        if ($user->id != $id) {
            return response()->json([
                'message' => 'Akses ditolak. ID tidak cocok dengan token.'
            ], 403);
        }

        $this->auth->logout();

        return response()->json([
            'message' => 'Logout berhasil.'
        ]);
    }

    public function refresh(): JsonResponse
    {
        $token = $this->auth->refresh();

        return response()->json([
            'message' => 'Token berhasil diperbarui.',
            'data' => [
                'access_token' => $token
            ]
        ]);
    }

    private function handleRegister(Request $request, string $role = 'pembeli'): JsonResponse
    {
        try {
            $data = $request->validate([
                'username' => 'required|string|unique:users,username',
                'email' => 'nullable|email|unique:users,email',
                'phone' => [
                    'nullable',
                    'regex:/^62[0-9]{9,13}$/',
                    'unique:users,phone'
                ],
                'password' => 'nullable|string|min:6',
            ]);

            if (empty($data['email']) && empty($data['phone'])) {
                throw ValidationException::withMessages([
                    'identifier' => ['Email atau nomor telepon wajib diisi.'],
                ]);
            }

            $result = $this->auth->register($data, $role);

            if (!empty($data['phone'])) {
                $this->otp->sendOtp($data['phone']);
            }

            return response()->json([
                'message' => 'Registrasi berhasil. OTP telah dikirim ke WhatsApp.',
                'data' => [
                    'id' => $result['user']->id,
                    'username' => $result['user']->username,
                    'email' => $result['user']->email,
                    'phone' => $result['user']->phone,
                    'role' => $result['user']->role,
                    'created_at' => $result['user']->created_at,
                ]
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validasi gagal.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan server.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    private function handleLogin(Request $request): JsonResponse
    {
        try {
            $data = $request->validate([
                'identifier' => 'required|string',
                'password' => 'required|string',
            ]);

            $result = $this->auth->login($data);
            $user = $result['user'];

            $hasPendingOtp = WhatsappOtp::where('phone', $user->phone)
                ->where('is_used', false)
                ->where('expires_at', '>=', now())
                ->exists();

            if ($hasPendingOtp) {
                return response()->json([
                    'message' => 'Silakan verifikasi OTP terlebih dahulu sebelum login.'
                ], 403);
            }

            $user->access_token = $result['token'];
            $user->save();

            return response()->json([
                'message' => 'Login berhasil.',
                'data' => [
                    'id' => $user->id,
                    'username' => $user->username,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'role' => $user->role,
                    'created_at' => $user->created_at,
                    'access_token' => $result['token'],
                ]
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validasi gagal.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan server.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
