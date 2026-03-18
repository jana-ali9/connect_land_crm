<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Unit;
use Illuminate\Http\Request;
use App\Http\Resources\UnitResource;

class ApiUnitController extends Controller
{
    /**
     * Build an absolute image URL depending on request scheme.
     * - If $raw already starts with http(s), return as-is.
     * - If HTTPS request → storage/public/app/...
     * - If HTTP request  → storage/...
     */
    private function buildImageUrl(?string $raw, Request $request): ?string
    {
        if (!$raw) {
            return null;
        }

        if (str_starts_with($raw, 'http://') || str_starts_with($raw, 'https://')) {
            return $raw;
        }

        // Detect HTTPS (works behind proxies if Trusted Proxies are configured)
        $isSecure = $request->isSecure()
            || strtolower($request->headers->get('x-forwarded-proto', '')) === 'https';

        $path = ltrim($raw, '/');

        return $isSecure
            ? asset('storage/app/public/' . $path)
            : asset('storage/' . $path);
    }

    /**
     * Mutate image-related keys in a row (unit/building arrays).
     */
    private function patchImageKeys(array &$row, Request $request): void
    {
        foreach (['photo', 'image', 'image_path'] as $key) {
            if (array_key_exists($key, $row) && $row[$key]) {
                $row[$key] = $this->buildImageUrl((string) $row[$key], $request);
            }
        }
    }

    public function index(Request $req)
    {
        $q = Unit::query()->with(['building:id,name,lat,lng,image_path']);

        // Filter: building_id
        if ($req->filled('building_id')) {
            $q->where('building_id', (int) $req->integer('building_id'));
        }

        // Filter: status -> is_rented
        if ($req->filled('status')) {
            $raw = strtolower((string) $req->input('status'));
            $map = [
                'rented'    => 1,
                '1'         => 1,
                'true'      => 1,
                'available' => 0,
                '0'         => 0,
                'false'     => 0,
            ];
            if (array_key_exists($raw, $map)) {
                $q->where('is_rented', $map[$raw]);
            }
        }

        // Filter: is_rented (explicit)
        if ($req->filled('is_rented')) {
            $q->where('is_rented', $req->integer('is_rented') ? 1 : 0);
        }

        // Search
        if ($s = trim((string) $req->input('search', ''))) {
            $q->where(function ($qq) use ($s) {
                $qq->where('name', 'like', "%$s%")
                   ->orWhere('number', 'like', "%$s%");
            });
        }

        $perPage = max((int) $req->integer('per_page', 20), 1);
        $items   = $q->latest()->simplePaginate($perPage);

        // Serialize via resource
        $rows = UnitResource::collection($items)->resolve();

        // Patch images for each row and nested building
        foreach ($rows as &$row) {
            $this->patchImageKeys($row, $req);
            if (isset($row['building']) && is_array($row['building'])) {
                $this->patchImageKeys($row['building'], $req);
            }
        }

        return response()->json([
            'data' => $rows,
            'meta' => [
                'current_page' => $items->currentPage(),
                'per_page'     => $items->perPage(),
                'next_page'    => $items->nextPageUrl(),
                'prev_page'    => $items->previousPageUrl(),
            ],
        ]);
    }

    public function show(Unit $unit, Request $request)
    {
        $unit->load(['building:id,name,lat,lng,image_path']);

        // Serialize one item
        $row = UnitResource::make($unit)->resolve();

        // Patch images for unit and nested building
        $this->patchImageKeys($row, $request);
        if (isset($row['building']) && is_array($row['building'])) {
            $this->patchImageKeys($row['building'], $request);
        }

        return response()->json($row);
    }
}
