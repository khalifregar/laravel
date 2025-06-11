<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();

            $table->string('username')->unique()->nullable();

            $table->string('email')->unique()->nullable();

            $table->string('phone')->unique()->nullable();

            $table->string('password')->nullable();

            $table->enum('role', ['admin', 'penjual', 'pembeli'])->default('pembeli');

            $table->string('access_token', 512)->nullable();

            $table->timestamp('last_login_at')->nullable();

            $table->rememberToken();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
