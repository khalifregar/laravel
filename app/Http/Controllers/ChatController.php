<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Services\MessageService;
use App\Events\MessageSent;

class ChatController extends Controller
{
    public function index($userId)
    {
        $authId = auth()->id();

        if ($userId == $authId) {
            return response()->json([
                'success' => false,
                'message' => 'Lu gak boleh ambil chat ke diri sendiri.'
            ], 403);
        }

        $service = new MessageService();
        $messages = $service->getConversationWith($userId);

        if ($messages->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Ga ada chat antara lu dan user itu.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $messages
        ]);
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'receiver_id' => 'required|exists:users,id',
                'content' => 'required|string'
            ]);

            if ($validated['receiver_id'] == auth()->id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Lu gak boleh kirim pesan ke diri sendiri.'
                ], 403);
            }

            $service = new MessageService();
            $message = $service->sendMessage(
                $validated['receiver_id'],
                $validated['content']
            );

            broadcast(new MessageSent($message))->toOthers();

            return response()->json([
                'success' => true,
                'message' => $message
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ], 500);
        }
    }
}
