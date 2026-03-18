<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'description', 'type','default_price'];

    public static function getTypes()
    {
        return ['service', 'feature'];
    }
    public function contractServices()
    {
        return $this->hasMany(ContractService::class, 'service_id');
    }

    public function contracts()
    {
        return $this->belongsToMany(Contract::class, 'contract_services')
                    ->withPivot('custom_price')
                    ->withTimestamps();
    }
}
