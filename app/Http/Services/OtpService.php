<?php

namespace App\Http\Services;

use App\Models\WhatsappOtp;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;

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

    // Simpan ke DB
    WhatsappOtp::create([
        'phone' => $phone,
        'otp' => $otp,
        'expires_at' => $expiresAt,
    ]);

    // Kirim via UltraMsg + logging responsenya
    $response = Http::get("https://api.ultramsg.com/{$this->ultraMsgInstance}/messages/chat", [
        'token'   => $this->ultraMsgToken,
        'to'      => $phone,
        'body'    => "Kode OTP kamu: $otp\nJangan bagikan ke siapa pun.\nBerlaku 5 menit.",
        'priority'=> 1,
    ]);

    // Log respon API, sukses atau gagal
    logger()->info('ULTRAMSG OTP RESPONSE', [
        'phone' => $phone,
        'otp' => $otp,
        'response_status' => $response->status(),
        'response_body' => $response->json(),
    ]);

    // Optional: log khusus kalau error
    if (!$response->ok() || ($response->json('status') ?? null) === 'error') {
        logger()->error('Gagal kirim OTP UltraMsg', [
            'phone' => $phone,
            'response' => $response->json(),
        ]);
    }
}


    public function verifyOtp(string $phone, string $otp): bool
    {
        $record = WhatsappOtp::where('phone', $phone)
            ->where('otp', $otp)
            ->where('is_used', false)
            ->where('expires_at', '>=', Carbon::now())
            ->latest()
            ->first();

        if (!$record) return false;

        $record->update(['is_used' => true]);
        return true;
    }
}
