<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Services\PropertyService;

class PropertyController extends Controller
{
    protected PropertyService $service;

    public function __construct(PropertyService $service)
    {
        $this->service = $service;
    }

    protected function jsonError(\Throwable $e, int $status = 500): JsonResponse
    {
        return response()->json([
            'message' => config('app.debug') ? $e->getMessage() : 'Terjadi kesalahan.',
        ], $status);
    }

    public function index(Request $request): JsonResponse
    {
        try {
            $properties = $this->service->allByUser(auth()->id());
            return response()->json($properties);
        } catch (\Throwable $e) {
            return $this->jsonError($e);
        }
    }

    public function show(string $propertyId): JsonResponse
    {
        try {
            $property = $this->service->findByPropertyIdAndUser($propertyId, auth()->id());
            return response()->json($property);
        } catch (\Throwable $e) {
            return $this->jsonError($e, 404);
        }
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'namaRumah' => 'required|string|max:255',
            'harga' => 'required|numeric|min:0',
            'tipeRumah' => 'required|in:rumah,apartemen,hotel,kos,villa,lainnya,tanah',
            'deskripsi' => 'nullable|string',
            'provinsi' => 'nullable|string|max:255',
            'kabupaten' => 'nullable|string|max:255',
            'kecamatan' => 'nullable|string|max:255',
            'kelurahan' => 'nullable|string|max:255',
        ]);

        try {
            $property = $this->service->create([
                'nama_rumah' => $data['namaRumah'],
                'harga' => $data['harga'],
                'tipe_rumah' => $data['tipeRumah'],
                'deskripsi' => $data['deskripsi'] ?? null,
                'provinsi' => $data['provinsi'] ?? null,
                'kabupaten' => $data['kabupaten'] ?? null,
                'kecamatan' => $data['kecamatan'] ?? null,
                'kelurahan' => $data['kelurahan'] ?? null,
                'user_id' => auth()->id(),
            ]);

            return response()->json([
                'message' => 'Property berhasil dibuat.',
                'data' => $property,
            ], 201);
        } catch (\Throwable $e) {
            return $this->jsonError($e);
        }
    }

    public function update(Request $request, string $propertyId): JsonResponse
    {
        $data = $request->validate([
            'namaRumah' => 'nullable|string|max:255',
            'harga' => 'nullable|numeric|min:0',
            'tipeRumah' => 'nullable|in:rumah,apartemen,hotel,kos,villa,lainnya,tanah',
            'deskripsi' => 'nullable|string',
            'provinsi' => 'nullable|string|max:255',
            'kabupaten' => 'nullable|string|max:255',
            'kecamatan' => 'nullable|string|max:255',
            'kelurahan' => 'nullable|string|max:255',
        ]);

        try {
            $updated = $this->service->updateByPropertyIdAndUser(
                $propertyId,
                auth()->id(),
                [
                    'nama_rumah' => $data['namaRumah'] ?? null,
                    'harga' => $data['harga'] ?? null,
                    'tipe_rumah' => $data['tipeRumah'] ?? null,
                    'deskripsi' => $data['deskripsi'] ?? null,
                    'provinsi' => $data['provinsi'] ?? null,
                    'kabupaten' => $data['kabupaten'] ?? null,
                    'kecamatan' => $data['kecamatan'] ?? null,
                    'kelurahan' => $data['kelurahan'] ?? null,
                ]
            );

            return response()->json([
                'message' => 'Property berhasil diperbarui.',
                'data' => $updated,
            ]);
        } catch (\Throwable $e) {
            return $this->jsonError($e, 403);
        }
    }

    public function destroy(string $propertyId): JsonResponse
    {
        try {
            $this->service->deleteByPropertyIdAndUser($propertyId, auth()->id());

            return response()->json([
                'message' => 'Property berhasil dihapus.',
            ]);
        } catch (\Throwable $e) {
            return $this->jsonError($e, 403);
        }
    }

    public function getPropertyTypes(): JsonResponse
    {
        return response()->json([
            'types' => $this->service->getAvailableTypes(),
        ]);
    }

    public function indexForPembeli(): JsonResponse
    {
        try {
            $properties = $this->service->all();
            return response()->json($properties);
        } catch (\Throwable $e) {
            return $this->jsonError($e);
        }
    }

    public function showForPembeli(string $propertyId): JsonResponse
    {
        try {
            $property = $this->service->findByPropertyId($propertyId);
            return response()->json($property);
        } catch (\Throwable $e) {
            return $this->jsonError($e, 404);
        }
    }

    public function filterByLocation(Request $request): JsonResponse
{
    $provinsi = $request->query('provinsi');
    $kabupaten = $request->query('kabupaten');
    $kecamatan = $request->query('kecamatan');
    $kelurahan = $request->query('kelurahan');

    try {
        $query = \App\Models\Property::query();

        if ($provinsi) {
            $query->where('provinsi', 'like', "%$provinsi%");
        }
        if ($kabupaten) {
            $query->where('kabupaten', 'like', "%$kabupaten%");
        }
        if ($kecamatan) {
            $query->where('kecamatan', 'like', "%$kecamatan%");
        }
        if ($kelurahan) {
            $query->where('kelurahan', 'like', "%$kelurahan%");
        }

        $results = $query->with('user')->get();

        return response()->json([
            'data' => $results,
        ]);
    } catch (\Throwable $e) {
        return $this->jsonError($e);
    }
}

}
