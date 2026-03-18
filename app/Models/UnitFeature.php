<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UnitFeature extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'image_path', 'type', 'unit_id'];

    public static function getTypes()
    {
        return ['text', 'image'];
    }
    public function getImageAttribute()
    {
        return $this->image_path ? asset('storage/' . $this->image_path) : null;
    }
}
