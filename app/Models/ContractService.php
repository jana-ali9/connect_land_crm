<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContractService extends Model
{
    use HasFactory;

    protected $fillable = ['contract_id', 'service_id', 'custom_price'];

    public function contract()
    {
        return $this->belongsTo(Contract::class, 'contract_id');
    }

    public function service()
    {
        return $this->belongsTo(Service::class, 'service_id');
    }
}
