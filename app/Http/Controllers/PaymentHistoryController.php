<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PaymentHistory;
use App\Models\Invoice;

class PaymentHistoryController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'invoice_id' => 'required|exists:invoices,id',
            'contract_id' => 'required|exists:contracts,id',
            'client_id' => 'required|exists:clients,id',
            'amount_paid' => 'required|numeric|min:0',
            'payment_date' => 'required|date',
        ]);

        $invoice = Invoice::find($request->invoice_id);

        $total_paid = $invoice->paymentHistories()->sum('amount_paid') + $request->amount_paid;
        $due_after_payment = $invoice->total_amount - $total_paid;

        $payment = PaymentHistory::create([
            'invoice_id' => $request->invoice_id,
            'contract_id' => $request->contract_id,
            'client_id' => $request->client_id,
            'amount_paid' => $request->amount_paid,
            'due_after_payment' => max($due_after_payment, 0),
            'payment_date' => $request->payment_date,
        ]);

        return redirect()->back()->with('success', 'Payment recorded successfully');
    }

    public function show($invoice_id)
    {
        $history = PaymentHistory::where('invoice_id', $invoice_id)->get();
        return view('payment_history.show', compact('history'));
    }
}
