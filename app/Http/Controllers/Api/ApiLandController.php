<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Land;
use Illuminate\Http\Request;
use App\Http\Resources\LandResource;

class ApiLandController extends Controller
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

        // Detect HTTPS (handles proxies if Trusted Proxies configured)
        $isSecure = $request->isSecure()
            || strtolower($request->headers->get('x-forwarded-proto', '')) === 'https';

        $path = ltrim($raw, '/');

        return $path;
    }

    /**
     * Mutate image-related keys in a row (land arrays).
     */
    private function patchImageKeys(array &$row, Request $request): void
    {
        // Common possible keys used by resources/models
        foreach (['photo', 'image', 'image_path', 'cover', 'thumbnail'] as $key) {
            if (array_key_exists($key, $row) && $row[$key]) {
                $row[$key] = $this->buildImageUrl((string) $row[$key], $request);
            }
        }
    }

    public function index(Request $req)
    {
        $q = Land::query();

        if ($s = $req->string('search')->toString()) {
            $q->where(function ($qq) use ($s) {
                $qq->where('name', 'like', "%$s%")
                   ->orWhere('location', 'like', "%$s%")
                   ->orWhere('address', 'like', "%$s%");
            });
        }

        if ($req->filled('status')) {
            $q->where('status', $req->string('status'));
        }

        $perPage = max((int) $req->integer('per_page', 20), 1);
        $items   = $q->latest()->simplePaginate($perPage);

        // Serialize via resource, then patch image URLs per scheme
        $rows = LandResource::collection($items)->resolve();
        foreach ($rows as &$row) {
            if (is_array($row)) {
                $this->patchImageKeys($row, $req);
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

    public function show(Land $land, Request $request)
    {
        $row = LandResource::make($land)->resolve();
        if (is_array($row)) {
            $this->patchImageKeys($row, $request);
        }

        return response()->json($row);
    }
}
