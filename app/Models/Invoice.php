<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = ['contract_id', 'client_id', 'invoice_date', 'services_cost', 'amount_due', 'amount_paid', 'status', 'type'];

    public function contract()
    {
        return $this->belongsTo(Contract::class, 'contract_id');
    }

    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function paymentHistories()
    {
        return $this->hasMany(PaymentHistory::class);
    }

    protected static function boot()
    {
        parent::boot();

        static::retrieved(function ($invoice) {
            if ($invoice->status === 'pending' && Carbon::parse($invoice->invoice_date)->isPast()) {
                $invoice->status = 'overdue';
                $invoice->saveQuietly(); // حفظ التغيير بدون إطلاق أحداث `saving` أو `saved`
            }
        });
    }
}
