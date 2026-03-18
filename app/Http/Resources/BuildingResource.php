<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

class BuildingResource extends JsonResource
{
    public function toArray($request)
    {
        // image might be absolute, storage path, or null
        $img = $this->image;
        if ($img && !Str::startsWith($img, ['http://','https://','/'])) {
            $img = 'storage/' . ltrim($img, '/');
        }

        return [
            'id'       => $this->id,
            'name'     => (string) ($this->name ?? ''),
            'location' => (string) ($this->location ?? $this->address ?? ''),
            'address'  => (string) ($this->address ?? ''),
            'country'  => (string) ($this->country ?? ''),
            'lat'      => $this->lat ? (float) $this->lat : null,
            'lng'      => $this->lng ? (float) $this->lng : null,
            'image'    => $img,  // app resolves relative via ApiService.resolveImageUrl
            'desc'     => (string) ($this->description ?? ''),
            // optional occupancy:
            'total_units'    => (int) ($this->total_units ?? 0),
            'occupied_units' => (int) ($this->occupied_units ?? 0),
        ];
    }
}
