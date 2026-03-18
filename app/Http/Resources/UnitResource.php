<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UnitResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $b = $this->whenLoaded('building');

        // Pick a building image field if present
        $buildingImage =
            optional($b)->image_path
            ?? optional($b)->photo
            ?? optional($b)->image
            ?? optional($b)->thumbnail;

        // Derive status string from is_rented
        $status = (int) $this->is_rented === 1 ? 'rented' : 'available';

        return [
            'id'             => $this->id,
            'name'           => $this->name,
            'building_id'    => $this->building_id,
            'status'         => $status,                     // derived for convenience

            // Fallbacks from building when unit fields are null
            'photo'          => $this->image_path ,
            'lat'            => $this->lat   ?? optional($b)->lat,
            'lng'            => $this->lng   ?? optional($b)->lng,

            // Nice-to-have extras for the UI
            'building_name'  => optional($b)->name,
        ];
    }
}
