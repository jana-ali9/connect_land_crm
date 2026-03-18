<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExpenseOffer extends Model
{
    use HasFactory;

    protected $fillable = [
        'expense_id',
        'company_name',
        'offer_amount',
        'status',
        'description'
    ];

    public function expense()
    {
        return $this->belongsTo(UnitExpense::class, 'expense_id');
    }

    // ✅ تحديث تلقائي عند تغيير الـ Status
    protected static function boot()
    {
        parent::boot();

        static::updated(function ($offer) {
            if ($offer->status == 1) {
                // ✅ تحديث العرض ليكون الوحيد المقبول
                $offer->expense->expenseOffers()->where('id', '!=', $offer->id)->update(['status' => 0]);

                // ✅ تحديث الـ amount في UnitExpense ليكون نفس offer_amount
                $offer->expense->updateAmountFromAcceptedOffer();
            }
        });
    }
}
