<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

class LandResource extends JsonResource
{
    public function toArray($request)
    {
        $photo = $this->photo;
        if ($photo && !Str::startsWith($photo, ['http://','https://','/'])) {
            $photo = 'storage/' . ltrim($photo, '/');
        }

        return [
            'id'       => $this->id,
            'name'     => (string) ($this->name ?? ''),
            'location' => (string) ($this->location ?? $this->address ?? ''),
            'address'  => (string) ($this->address ?? ''),
            'country'  => (string) ($this->country ?? ''),
            'lat'      => $this->lat ? (float) $this->lat : null,
            'lng'      => $this->lng ? (float) $this->lng : null,
            'photo'    => $photo,
            'area'     => (float) ($this->area ?? 0),
            'status'   => (string) ($this->status ?? 'available'),
            'desc'     => (string) ($this->description ?? ''),
        ];
    }
}
