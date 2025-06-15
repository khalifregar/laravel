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
        'provinsi',
        'kabupaten',
        'kecamatan',
        'kelurahan',
    ];

    protected $casts = [
        'harga' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getFullLokasiAttribute(): string
    {
        return implode(', ', array_filter([
            $this->kelurahan,
            $this->kecamatan,
            $this->kabupaten,
            $this->provinsi,
        ]));
    }
}
