<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Building;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ApiBuildingController extends Controller
{
    /**
     * Build an absolute image URL depending on request scheme.
     * - If $raw starts with http(s), return as-is.
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

        // Detect HTTPS (honors Trusted Proxies). Add a fallback to X-Forwarded-Proto.
        $isSecure = $request->isSecure()
            || strtolower($request->headers->get('x-forwarded-proto', '')) === 'https';

        $path = ltrim($raw, '/');

        return $isSecure
            ? asset('storage/app/public/' . $path)
            : asset('storage/' . $path);
    }

    public function index(Request $request)
    {
        $perPage = (int) $request->get('per_page', 12);

        $q = Building::query()
            ->select([
                'buildings.*',
                DB::raw('(select count(*) from units where units.building_id = buildings.id) as total_units'),
                DB::raw('(select count(*) from units where units.building_id = buildings.id and units.is_rented = 1) as occupied_units'),
            ])
            ->orderByDesc('created_at');

        $p = $q->paginate($perPage);

        $data = collect($p->items())->map(function ($b) use ($request) {
            $rawImage = $b->image_path ?? $b->image ?? $b->photo ?? null;
            $image = $this->buildImageUrl($rawImage, $request);

            $total = (int) ($b->total_units ?? 0);
            $occ   = (int) ($b->occupied_units ?? 0);
            $rate  = $total > 0 ? round(($occ / $total) * 100) : 0;

            return [
                'id'             => (int) $b->id,
                'name'           => (string) ($b->name ?? ''),
                'desc'           => (string) ($b->description ?? ''),
                'address'        => $b->address,
                'country'        => $b->country,
                'location'       => $b->location,
                'lat'            => isset($b->lat) ? (float) $b->lat : null,
                'lng'            => isset($b->lng) ? (float) $b->lng : null,
                'image'          => $image,
                'total_units'    => $total,
                'occupied_units' => $occ,
                'occupancy_rate' => $rate,
                'is_payed'       => (int) ($b->is_payed ?? 0),
            ];
        })->values();

        return response()->json([
            'data' => $data,
            'meta' => [
                'current_page' => $p->currentPage(),
                'last_page'    => $p->lastPage(),
                'total'        => $p->total(),
            ],
        ]);
    }

    public function show(Building $building, Request $request)
    {
        // compute counts for this building
        $total = $building->units()->count();
        $occ   = $building->units()->where('is_rented', 1)->count();
        $rate  = $total ? round($occ / $total * 100) : 0;

        $rawImage = $building->image_path ?? $building->image ?? $building->photo ?? null;
        $image = $this->buildImageUrl($rawImage, $request);

        return response()->json([
            'id'             => (int) $building->id,
            'name'           => (string) ($building->name ?? ''),
            'desc'           => (string) ($building->description ?? ''),
            'address'        => $building->address,
            'country'        => $building->country,
            'location'       => $building->location,
            'lat'            => isset($building->lat) ? (float) $building->lat : null,
            'lng'            => isset($building->lng) ? (float) $building->lng : null,
            'image'          => $image,
            'total_units'    => $total,
            'occupied_units' => $occ,
            'occupancy_rate' => $rate,
            'is_payed'       => (int) ($building->is_payed ?? 0),
        ]);
    }

    /**
     * GET /api/buildings/{building}/units
     * Returns the units that belong to this building.
     */
    public function units(Building $building, Request $request)
    {
        $perPage = (int) $request->get('per_page', 20);

        $p = $building->units()
            ->select(['id','name','image_path','description','area','is_rented','building_id','start_price','end_price'])
            ->orderByDesc('created_at')
            ->paginate($perPage);

        $rows = collect($p->items())->map(function ($u) use ($building, $request) {
            $raw = $u->image_path ?? null; // column exists in DB
            $image = $this->buildImageUrl($raw, $request);

            return [
                'id'            => (int) $u->id,
                'name'          => (string) ($u->name ?? ''),
                'description'   => $u->description,
                'area'          => $u->area,
                'is_rented'     => (int) $u->is_rented === 1,
                'building_id'   => (int) ($u->building_id ?? $building->id),
                'building_name' => (string) ($building->name ?? ''),
                'photo'         => $image,              // normalized URL for Flutter
                'start_price'   => $u->start_price,
                'end_price'     => $u->end_price,
            ];
        })->values();

        return response()->json([
            'data' => $rows,
            'meta' => [
                'current_page' => $p->currentPage(),
                'last_page'    => $p->lastPage(),
                'total'        => $p->total(),
            ],
        ]);
    }
}
