<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Land extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'location',
        'photo',
        'area',
        'property_number',
        'section_number',
        'district_zone',
        'address',
        'country',
        'lat',
        'lng'
    ];



    public function getPhotoUrlAttribute()
    {
        return $this->photo ? asset('storage/' . $this->photo) : asset('images/default-land.png');
    }
}
