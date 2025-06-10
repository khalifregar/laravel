<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Message;
use App\Events\MessageSent;

Route::get('/', fn() => view('welcome'))->middleware('web');

Route::post('/web-register', function (Request $request) {
    $request->validate([
        'email' => 'required|email|unique:users',
        'password' => 'required|min:6',
        'role' => 'required|in:penjual,pembeli'
    ]);

    $user = User::create([
        'email' => $request->email,
        'password' => Hash::make($request->password),
        'role' => $request->role
    ]);

    Auth::login($user);
    return redirect('/');
});

Route::post('/web-login', function (Request $request) {
    if (Auth::attempt($request->only('email', 'password'))) {
        return redirect('/');
    }
    return back()->with('error', 'Login gagal, cek email/password.');
});

Route::post('/logout', function () {
    Auth::logout();
    return redirect('/');
});

Route::get('/test-broadcast', function () {
    if (!auth()->check()) {
        return redirect('/');
    }

    $fakeMessage = new Message([
        'id' => 9999,
        'sender_id' => auth()->id(),
        'receiver_id' => auth()->id(), // kirim ke diri sendiri
        'content' => 'Halo ini pesan test dari route!',
        'created_at' => now(),
    ]);

    // ✅ Hapus ->toOthers() supaya user ini juga nerima broadcast-nya
    broadcast(new MessageSent($fakeMessage));

    return '✅ Pesan test berhasil dikirim ke channel chat.' . auth()->id();
})->middleware('auth');
