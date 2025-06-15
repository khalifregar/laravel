<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MidtransWebhookController extends Controller
{
    public function handle(Request $request)
    {
        // Log semua payload dari Midtrans
        Log::info('Webhook Received:', $request->all());

        // Contoh proses data
        $orderId = $request->input('order_id');
        $status = $request->input('transaction_status');
        $type = $request->input('payment_type');
        $fraud = $request->input('fraud_status');

        // Simpan atau update status transaksi lo di database (jika ada)

        return response()->json(['message' => 'OK']);
    }
}
