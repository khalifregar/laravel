<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Property extends Model
{
    protected $table = 'properties';

    protected $fillable = [
        'property_id',
        'user_id',
        'nama_rumah',
        'harga',
        'tipe_rumah',
        'deskripsi',
        'lokasi',
    ];

    protected $casts = [
        'harga' => 'integer',
    ];

    /**
     * Relasi ke user (penjual atau admin)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
