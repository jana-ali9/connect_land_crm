<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UnitExpense extends Model
{
    use HasFactory;

    protected $fillable = ['expense_name','land_id' ,'unit_id', 'building_id', 'amount', 'allocation_type', 'description','category_id'];

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function expenseOffers()
    {
        return $this->hasMany(ExpenseOffer::class, 'expense_id');
    }

    public function building()
    {
        return $this->belongsTo(Building::class);
    }

    public function land()
{
    return $this->belongsTo(Land::class);
}

    public function expenseCategory()
    {
        return $this->belongsTo(ExpenseCategory::class, 'category_id');
    }

    // ✅ تحديث الـ amount بناءً على العرض المقبول
    public function updateAmountFromAcceptedOffer()
    {
        $acceptedOffer = $this->expenseOffers()->where('status', 1)->first();
        if ($acceptedOffer) {
            $this->update(['amount' => $acceptedOffer->offer_amount]);
        }
    }
}
