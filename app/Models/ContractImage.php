<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContractImage extends Model
{
    use HasFactory;
    protected $fillable = ['contract_id', 'image_path'];

    public function contract()
    {
        return $this->belongsTo(Contract::class);
    }

    public function getImageAttribute()
    {
        return asset('storage/'.$this->image_path);
    }
}
