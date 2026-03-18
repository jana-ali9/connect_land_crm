<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Building extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'image_path',
        'address',
        'location',
        'start_price',
        'end_price',
        'is_payed',
        'address',
        'country',
        'lat',
        'lng'
    ];

    public function getImageAttribute()
    {
        return asset('storage/' . $this->image_path);
    }
    public function expenses()
    {
        return $this->hasMany(UnitExpense::class);
    }
    public function units()
    {
        return $this->hasMany(Unit::class);
    }
    public function getRentedUnitsCountAttribute()
    {
        return $this->units()->where('is_rented', true)->count();
    }
    public function getPayedUnitsCountAttribute()
    {
        return $this->units()->where('is_payed', false)->count();
    }

public function getImageUrlAttribute()
{
    if (!$this->image_path) return null;
    return str_starts_with($this->image_path, 'http')
        ? $this->image_path
        : asset('storage/' . ltrim($this->image_path, '/'));
}

}
