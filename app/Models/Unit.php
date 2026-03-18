<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Unit extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'image_path',
        'area',
        'building_id',
        'is_rented',
        'start_price',
        'end_price',
        'is_payed'
    ];
    public function building(): BelongsTo
    {
        return $this->belongsTo(Building::class);
    }
    public function expenses()
    {
        return $this->hasMany(UnitExpense::class);
    }
    public function allExpenses()
    {
        return UnitExpense::where(function ($query) {
            $query->where('unit_id', $this->id)
                ->orWhere('building_id', $this->building_id);
        })
            ->get()
            ->map(function ($expense) {
                if ($expense->allocation_type == 'building' && count($this->building->units) > 0) {
                    $expense->amount = $expense->amount / count($this->building->units); // ✅ تقسيم على عدد الوحدات
                }
                return $expense;
            });
    }



    public function getImageAttribute()
    {
        return asset('storage/' . $this->image_path);
    }
    /* public function getImageAttribute()
    {
        return asset('storage/app/public/' . $this->image_path);
    } */
    public function features()
    {
        return $this->hasMany(UnitFeature::class, 'unit_id');
    }
    public function contract()
    {
        return $this->hasOne(Contract::class, 'unit_id')->latest();
    }
    public function paidInvoices()
    {
        return $this->hasManyThrough(
            Invoice::class,
            Contract::class,
            'unit_id',
            'contract_id',
            'id',
            'id'
        )->where('status', 'paid')->where('type','unit');
    }
}
