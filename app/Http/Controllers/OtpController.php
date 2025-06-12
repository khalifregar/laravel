<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Services\OtpService;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class OtpController extends Controller
{
    protected OtpService $otp;

    public function __construct(OtpService $otp)
    {
        $this->otp = $otp;
    }

    public function send(Request $request): JsonResponse
    {
        try {
            $data = $request->validate([
                'phone' => [
                    'required',
                    'regex:/^62[0-9]{9,13}$/'
                ]
            ]);

            $this->otp->sendOtp($data['phone']);

            return response()->json([
                'message' => 'OTP berhasil dikirim ke WhatsApp.'
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validasi gagal.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Gagal mengirim OTP.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

public function verify(Request $request): JsonResponse
{
    try {
        $data = $request->validate([
            'phone' => [
                'required',
                'regex:/^62[0-9]{9,13}$/'
            ],
            'otp' => 'required|digits:6'
        ]);

        $valid = $this->otp->verifyOtp($data['phone'], $data['otp']);

        if (!$valid) {
            return response()->json([
                'message' => 'OTP tidak valid atau telah kadaluarsa.',
                'errors' => [
                    'otp' => ['OTP tidak valid atau telah kadaluarsa.']
                ]
            ], 422);
        }

        // ✅ Login user setelah OTP diverifikasi
        $user = \App\Models\User::where('phone', $data['phone'])->first();

        if (!$user) {
            return response()->json([
                'message' => 'User tidak ditemukan.'
            ], 404);
        }

        $token = \Tymon\JWTAuth\Facades\JWTAuth::fromUser($user);

        return response()->json([
            'message' => 'OTP berhasil diverifikasi.',
            'token' => $token,
            'user' => $user
        ]);
    } catch (ValidationException $e) {
        return response()->json([
            'message' => 'Validasi gagal.',
            'errors' => $e->errors()
        ], 422);
    } catch (\Throwable $e) {
        return response()->json([
            'message' => 'Terjadi kesalahan saat verifikasi OTP.',
            'error' => $e->getMessage()
        ], 500);
    }
}

}
