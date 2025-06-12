<?php

namespace App\Http\Services;

use App\Models\User;
use App\Models\WhatsappOtp;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Tymon\JWTAuth\Facades\JWTAuth;

class OtpService
{
    protected string $ultraMsgInstance;
    protected string $ultraMsgToken;

    public function __construct()
    {
        $this->ultraMsgInstance = config('services.ultramsg.instance');
        $this->ultraMsgToken = config('services.ultramsg.token');
    }

    public function sendOtp(string $phone): void
    {
        $otp = rand(100000, 999999);
        $expiresAt = now()->addMinutes(5);

        WhatsappOtp::create([
            'phone' => $phone,
            'otp' => $otp,
            'expires_at' => $expiresAt,
        ]);

        $response = Http::get("https://api.ultramsg.com/{$this->ultraMsgInstance}/messages/chat", [
            'token'    => $this->ultraMsgToken,
            'to'       => $phone,
            'body'     => "Kode OTP kamu: $otp\nJangan bagikan ke siapa pun.\nBerlaku 5 menit.",
            'priority' => 1,
        ]);

        logger()->info('ULTRAMSG OTP RESPONSE', [
            'phone' => $phone,
            'otp' => $otp,
            'response_status' => $response->status(),
            'response_body' => $response->json(),
        ]);

        if (!$response->ok() || ($response->json('status') ?? null) === 'error') {
            logger()->error('Gagal kirim OTP UltraMsg', [
                'phone' => $phone,
                'response' => $response->json(),
            ]);
        }
    }

    public function verifyOtp(string $phone, string $otp): ?array
    {
        $record = WhatsappOtp::where('phone', $phone)
            ->where('otp', $otp)
            ->where('is_used', false)
            ->where('expires_at', '>=', Carbon::now())
            ->latest()
            ->first();

        if (!$record) return null;

        $record->update(['is_used' => true]);

        $user = User::where('phone', $phone)->first();

        if (!$user) return null;

        $token = JWTAuth::fromUser($user);

        return [
            'token' => $token,
            'user' => $user,
        ];
    }
}
