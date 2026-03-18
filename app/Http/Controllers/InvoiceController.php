<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Contract;
use App\Models\Invoice;
use App\Models\Land;
use App\Models\Building;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InvoiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Invoice::query();

        // 🔎 فلتر العميل
        if ($request->filled('client_id')) {
            $query->where('client_id', $request->client_id);
        }

        // 🔎 فلتر الحالة
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // 💰 فلتر السعر بين حدين
        if ($request->filled('min_price')) {
            $query->where('amount_due', '>=', $request->min_price);
        }
        if ($request->filled('max_price')) {
            $query->where('amount_due', '<=', $request->max_price);
        }

        // 🗓️ فلتر التاريخ بين حدين
        if ($request->filled('start_date')) {
            $query->whereDate('invoice_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('invoice_date', '<=', $request->end_date);
        }

        // ✅ فلتر النوع (service/rent/sale)
        if ($request->filled('type')) {
            if ($request->type == 'service') {
                $query->where('type', 'service');
            } else {
                $query->where('type', '!=', 'service') // معناها إنها مرتبطة بعقد
                    ->whereHas('contract', function ($q) use ($request) {
                        $q->where('contract_type', $request->type);
                    });
            }
        }

        // ⏳ ترتيب الفواتير
        $query->orderByRaw("
    CASE status
        WHEN 'overdue' THEN 1
        WHEN 'pending' THEN 2
        WHEN 'paid' THEN 3
        WHEN 'suspended' THEN 4
        ELSE 5
    END
")->orderBy('invoice_date', 'ASC');

        // 🛑 تنفيذ الاستعلام
        $allinvoices = $query->paginate(20)->appends($request->query());

        // 🔄 العملاء
        $clients = Client::all();

        return view('invoices.index', compact('allinvoices', 'clients'));
    }

public function history(Request $request)
{
    $clients = Client::all();
    $buildings = Building::all();
    $lands = Land::all(); // ✅ for land dropdown

    $query = \App\Models\PaymentHistory::with([
        'client',
        'contract.unit',
        'contract.building',
        'contract.land',
        'invoice'
    ])->where('amount_paid', '>', 0); // ✅ only payments with amount > 0

    // Apply filters
    if ($request->filled('client_id')) {
        $query->where('client_id', $request->client_id);
    }

    if ($request->filled('building_id')) {
        $query->whereHas('contract', function ($q) use ($request) {
            $q->where('building_id', $request->building_id);
        });
    }

    if ($request->filled('unit_id')) {
        $query->whereHas('contract', function ($q) use ($request) {
            $q->where('unit_id', $request->unit_id);
        });
    }

    if ($request->filled('land_id')) {
        $query->whereHas('contract', function ($q) use ($request) {
            $q->where('land_id', $request->land_id);
        });
    }

    if ($request->filled('start_date')) {
        $query->whereDate('payment_date', '>=', $request->start_date);
    }

    if ($request->filled('end_date')) {
        $query->whereDate('payment_date', '<=', $request->end_date);
    }

    // Calculate total paid after filtering
    $totalPaid = (clone $query)->sum('amount_paid');

    // Paginate results
    $allPayments = $query
        ->orderBy('payment_date', 'desc')
        ->paginate(20)
        ->appends($request->query());

    return view('invoices.history', compact(
        'allPayments',
        'clients',
        'buildings',
        'lands',
        'totalPaid'
    ));
}


    public function viewInvoice($id)
    {
        $invoice = Invoice::findOrFail($id); // جلب الفاتورة من قاعدة البيانات

        return view('invoices.view', compact('invoice'));
    }
    public function services(Request $request)
    {
        $validated = $request->validate([
            'invoice_id' => 'required|exists:invoices,id',
            'custom_price' => 'required|numeric|min:0'
        ]);

        $invoice = Invoice::findOrFail($request->invoice_id);
        $allcost = $invoice->contract->amount_for_services + $request->custom_price;

        if ($invoice->services_cost > $allcost) {
            session()->flash('errors', "The price is less than the cost of services.");
            return redirect()->back();
        }

        $today = now();
        $invoice->update([
            'amount_paid' => $invoice->services_cost,
            'status' => 'paid',
            'type' => 'service',
        ]);

        // ✅ ADD payment_history here
        \App\Models\PaymentHistory::create([
            'invoice_id' => $invoice->id,
            'contract_id' => $invoice->contract_id,
            'client_id' => $invoice->client_id,
            'amount_paid' => $invoice->services_cost,
            'due_after_payment' => 0, // services paid in full
            'payment_date' => $today,
        ]);

        if (($allcost - $invoice->services_cost) > 0) {
            $invoice->contract->amount_for_services = $allcost - $invoice->services_cost;
            $invoice->contract->services_date = $today->toDateString();
            $invoice->contract->save();
        }

        session()->flash('success', "Services have been paid successfully.");

        return redirect()->back();
    }


    public function storePaymentHistory(Request $request, $invoiceId)
    {
        $request->validate([
            'amount_paid' => 'required|numeric|min:0',
            'payment_date' => 'required|date',
        ]);

        $invoice = Invoice::findOrFail($invoiceId);

        $total_paid = $invoice->paymentHistories()->sum('amount_paid') + $request->amount_paid;
        $due_after_payment = $invoice->total_amount - $total_paid;

        \App\Models\PaymentHistory::create([
            'invoice_id' => $invoice->id,
            'contract_id' => $invoice->contract_id,
            'client_id' => $invoice->client_id,
            'amount_paid' => $request->amount_paid,
            'due_after_payment' => max($due_after_payment, 0),
            'payment_date' => $request->payment_date,
        ]);

        return redirect()->back()->with('success', 'Payment recorded successfully.');
    }

    public function showPaymentHistory($invoiceId)
    {
        $history = \App\Models\PaymentHistory::where('invoice_id', $invoiceId)->get();
        return view('invoice.payment_history', compact('history'));
    }

    public function update(Request $request, Invoice $invoice)
    {
        try {
            $validated = $request->validate([
                'status' => 'required|in:pending,paid,overdue',
            ]);

            $newStatus = $validated['status'];

            if ($newStatus === 'overdue' || $newStatus === 'paid' || $newStatus === 'pending') {
                $invoice->status = 'paid';
                $invoice->amount_paid = $invoice->amount_due + $invoice->services_cost;

                // ✅ ADD payment_history
                \App\Models\PaymentHistory::create([
                    'invoice_id' => $invoice->id,
                    'contract_id' => $invoice->contract_id,
                    'client_id' => $invoice->client_id,
                    'amount_paid' => $invoice->amount_paid,
                    'due_after_payment' => 0,
                    'payment_date' => now(),
                ]);
            }

            $invoice->save();

            return response()->json([
                'success' => true,
                'message' => 'Invoice updated successfully.',
                'new_status' => $invoice->status,
                'amount_paid' => $invoice->amount_paid,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Server error: ' . $e->getMessage(),
            ], 500);
        }
    }


    public function payByContract(Request $request)
    {
        $request->validate([
            'contract_id' => 'required|exists:contracts,id',
            'amount' => 'required|numeric|min:0.01'
        ]);

        $contract = Contract::findOrFail($request->contract_id);
        $amount = $request->amount;

        $result = $contract->distributePayment($amount);

        return back()->with('status', $result);
    }
}
