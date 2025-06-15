<?php

namespace App\Http\Services;

use App\Models\Property;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Str;

class PropertyService
{
    public function all(): Collection
    {
        return Property::with('user')->get();
    }

    public function allByUser(int $userId): Collection
    {
        return Property::with('user')
            ->where('user_id', $userId)
            ->get();
    }

    public function find(int $id): Property
    {
        $property = Property::with('user')->find($id);

        if (!$property) {
            throw new ModelNotFoundException("Property dengan ID $id tidak ditemukan.");
        }

        return $property;
    }

    public function findByPropertyId(string $propertyId): Property
    {
        $property = Property::with('user')
            ->where('property_id', $propertyId)
            ->first();

        if (!$property) {
            throw new ModelNotFoundException("Property dengan property_id $propertyId tidak ditemukan.");
        }

        return $property;
    }

    public function create(array $data): Property
    {
        return Property::create([
            'property_id' => (string) Str::uuid(),
            'user_id' => $data['user_id'],
            'nama_rumah' => $data['nama_rumah'],
            'harga' => $data['harga'],
            'tipe_rumah' => $data['tipe_rumah'],
            'deskripsi' => $data['deskripsi'] ?? null,
            'provinsi' => $data['provinsi'] ?? null,
            'kabupaten' => $data['kabupaten'] ?? null,
            'kecamatan' => $data['kecamatan'] ?? null,
            'kelurahan' => $data['kelurahan'] ?? null,
        ]);
    }

    public function getAvailableTypes(): array
    {
        return ['rumah', 'apartemen', 'hotel', 'kos', 'villa', 'lainnya', 'tanah'];
    }

    public function updateByPropertyId(string $propertyId, array $data): Property
    {
        $property = Property::where('property_id', $propertyId)->first();

        if (!$property) {
            throw new ModelNotFoundException("Property dengan ID $propertyId tidak ditemukan.");
        }

        $property->update([
            'nama_rumah' => $data['nama_rumah'] ?? $property->nama_rumah,
            'harga' => $data['harga'] ?? $property->harga,
            'tipe_rumah' => $data['tipe_rumah'] ?? $property->tipe_rumah,
            'deskripsi' => $data['deskripsi'] ?? $property->deskripsi,
            'provinsi' => $data['provinsi'] ?? $property->provinsi,
            'kabupaten' => $data['kabupaten'] ?? $property->kabupaten,
            'kecamatan' => $data['kecamatan'] ?? $property->kecamatan,
            'kelurahan' => $data['kelurahan'] ?? $property->kelurahan,
        ]);

        return $property;
    }

    public function updateByPropertyIdAndUser(string $propertyId, int $userId, array $data): Property
    {
        $property = Property::where('property_id', $propertyId)
            ->where('user_id', $userId)
            ->first();

        if (!$property) {
            throw new ModelNotFoundException("Property tidak ditemukan atau bukan milik Anda.");
        }

        $property->update([
            'nama_rumah' => $data['nama_rumah'] ?? $property->nama_rumah,
            'harga' => $data['harga'] ?? $property->harga,
            'tipe_rumah' => $data['tipe_rumah'] ?? $property->tipe_rumah,
            'deskripsi' => $data['deskripsi'] ?? $property->deskripsi,
            'provinsi' => $data['provinsi'] ?? $property->provinsi,
            'kabupaten' => $data['kabupaten'] ?? $property->kabupaten,
            'kecamatan' => $data['kecamatan'] ?? $property->kecamatan,
            'kelurahan' => $data['kelurahan'] ?? $property->kelurahan,
        ]);

        return $property;
    }

    public function deleteByPropertyId(string $propertyId): bool
    {
        $property = Property::where('property_id', $propertyId)->first();

        if (!$property) {
            throw new ModelNotFoundException("Property dengan ID $propertyId tidak ditemukan.");
        }

        return $property->delete();
    }

    public function deleteByPropertyIdAndUser(string $propertyId, int $userId): bool
    {
        $property = Property::where('property_id', $propertyId)
            ->where('user_id', $userId)
            ->first();

        if (!$property) {
            throw new ModelNotFoundException("Property tidak ditemukan atau bukan milik Anda.");
        }

        return $property->delete();
    }

    public function filterByType(string $tipe): Collection
    {
        return Property::with('user')
            ->where('tipe_rumah', $tipe)
            ->get();
    }
}
