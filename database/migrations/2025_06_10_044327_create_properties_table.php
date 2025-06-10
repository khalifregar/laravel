<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('properties', function (Blueprint $table) {
            $table->id();
            $table->uuid('property_id')->unique();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('nama_rumah');
            $table->unsignedBigInteger('harga');
            $table->enum('tipe_rumah', ['rumah', 'apartemen', 'hotel', 'kos', 'villa', 'lainnya']);
            $table->text('deskripsi')->nullable();
            $table->string('lokasi')->nullable();
            $table->timestamps();
        });
    }

public function down(): void
{
    Schema::dropIfExists('properties'); // ✅ FIXED
}

};
