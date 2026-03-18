<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Contract extends Model
{
    use HasFactory;
    protected $fillable = ['property_type','unit_id', 'land_id', 'building_id', 'client_id', 'start_date', 'end_date', 'base_rent', 'insurance', 'contract_video', 'increase_rate', 'increase_frequency', 'contract_status', 'contract_type', 'amount_for_services', 'services_date', 'billing_frequency', 'invoice_price'];

    protected static function boot()
    {
        parent::boot();

        static::retrieved(function ($contract) {
            if ($contract->end_date && Carbon::parse($contract->end_date)->isPast()) {
                $contract->update(['contract_status' => 'expired']);
            }
        });
    }
    // في موديل Contract
    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }
    public function building()
    {
        return $this->belongsTo(Building::class);
    }
    public function land()
{
    return $this->belongsTo(Land::class);
}

    public function client()
    {
        return $this->belongsTo(Client::class);
    }
    public function invoices()
    {
        return $this->hasMany(Invoice::class, 'contract_id');
    }

    public function services()
    {
        return $this->belongsToMany(Service::class, 'contract_services')
            ->withPivot('id', 'custom_price')
            ->withTimestamps();
    }
    public function calculateCurrentRent()
    {
        $months_passed = Carbon::parse($this->start_date)->diffInMonths(Carbon::now());
        $increase_cycles = floor($months_passed / $this->increase_frequency);
        $current_rent = $this->base_rent * pow(1 + ($this->increase_rate / 100), $increase_cycles);
        return round($current_rent, 2);
    }
    public function images()
    {
        return $this->hasMany(ContractImage::class, 'contract_id');
    }

    public function paymentHistories()
    {
        return $this->hasMany(PaymentHistory::class);
    }

    public function calculateTotalRent()
    {
        $service_total = $this->services()->sum('custom_price');
        return $this->calculateCurrentRent() + $service_total;
    }
    public function getVideoAttribute()
    {
        return $this->contract_video ? asset('storage/' . $this->contract_video) : null;
    }public function distributePayment($amount)
{
    $invoices = $this->invoices()
        ->whereIn('status', ['pending', 'overdue'])
        ->where('type', 'unit')
        ->orderBy('invoice_date')
        ->get();

    $initialAmount = $amount;

    foreach ($invoices as $invoice) {
        $remaining = ($invoice->amount_due + $invoice->services_cost) - $invoice->amount_paid;

        if ($remaining <= 0)
            continue;

        if ($amount >= $remaining) {
            $invoice->amount_paid += $remaining;
            $invoice->status = 'paid';
            $paidNow = $remaining;
            $amount -= $remaining;
        } else {
            $invoice->amount_paid += $amount;
            $invoice->status = 'pending'; // still not fully paid
            $paidNow = $amount;
            $amount = 0;
        }

        $invoice->save();

        // ✅ store in payment_history
        \App\Models\PaymentHistory::create([
            'invoice_id' => $invoice->id,
            'contract_id' => $this->id,
            'client_id' => $this->client_id,
            'amount_paid' => $paidNow,
            'due_after_payment' => max(($invoice->amount_due + $invoice->services_cost) - $invoice->amount_paid, 0),
            'payment_date' => now(),
        ]);

        if ($amount <= 0)
            break;
    }

    if ($amount > 0) {
        return "Payment has been completed successfully, and there is an unused amount of: " . number_format($amount, 2);
    }

    return "The amount has been successfully distributed to the invoices (Amount paid: " . number_format($initialAmount, 2) . ")";
}

}
