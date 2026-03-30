<?php


namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Building;
use App\Models\Contract;
use App\Models\Invoice;
use App\Models\Land;
use App\Models\PaymentHistory;
use App\Models\UnitExpense;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
class RoutingController extends Controller
{
    public function root(Request $request)
    {
        $today = now();
        $startOfWeek = $today->copy()->startOfWeek();
        $startOfMonth = $today->copy()->startOfMonth();
        $startOfYear = $today->copy()->startOfYear();
        $futureDate = $today->copy()->addDays(10);
        $expiringDate = $today->copy()->addDays(30);

        // Determine filter range
        $filter = $request->get('filter', 'all');
        $startDate = null;
        $endDate = null;
        if ($filter === 'custom') {
            if ($request->has('start_date') && $request->has('end_date')) {
                $startDate = Carbon::parse($request->get('start_date'))->startOfDay();
                $endDate   = Carbon::parse($request->get('end_date'))->endOfDay();
            }
        } else {
            $startDate = match ($filter) {
                'week'  => $today->copy()->startOfWeek(),
                'month' => $today->copy()->startOfMonth(),
                'year'  => $today->copy()->startOfYear(),
                default => null,
            };
        }
       

        // Payments
      $filteredPayments = PaymentHistory::query();
if ($startDate) {
    if ($endDate) {
        $filteredPayments->whereBetween('created_at', [$startDate, $endDate]);
    } else {
        $filteredPayments->where('created_at', '>=', $startDate);
    }
        }
        $currentAmountPaid = $filteredPayments->sum('amount_paid');

        // Invoices
        $filteredInvoices = Invoice::query();
        if ($startDate) {
        if ($endDate) {
                $filteredInvoices->whereBetween('invoice_date', [$startDate, $endDate]);
            } else {    
            $filteredInvoices->where('invoice_date', '>=', $startDate);
        }
        }
        $currentAmountDue = $filteredInvoices->sum(DB::raw('amount_due + services_cost - amount_paid'));

        // Expenses
        $filteredExpenses = UnitExpense::query();
        if ($startDate) {
        if ($endDate) {
                $filteredExpenses->whereBetween('created_at', [$startDate, $endDate]);
            } else {    
            $filteredExpenses->where('created_at', '>=', $startDate);
        }
        }
        $currentExpenses = $filteredExpenses->sum('amount');

        // Static data
        $allbuildings = Building::query();
if ($startDate) {
    if ($endDate) {
        $allbuildings->whereBetween('created_at', [$startDate, $endDate]);
    } else {
        $allbuildings->where('created_at', '>=', $startDate);
    }
}
$allbuildings = $allbuildings->get();


$alllands = Land::query();
if ($startDate) {
    if ($endDate) {
        $alllands->whereBetween('created_at', [$startDate, $endDate]);
    } else {
        $alllands->where('created_at', '>=', $startDate);
    }
}
$alllands = $alllands->get();

        $activeContractsCount = Contract::where('contract_status', 'active');

if ($startDate) {
    if ($endDate) {
        $activeContractsCount->whereBetween('start_date', [$startDate, $endDate]);
    } else {
        $activeContractsCount->where('start_date', '>=', $startDate);
    }
}

$activeContractsCount = $activeContractsCount
    ->whereDate('end_date', '>=', $today)
    ->count();

        $expiringContracts = Contract::where('contract_status', 'active')
            ->whereBetween('end_date', [$today, $expiringDate])
            ->orderBy('end_date', 'ASC')
            ->get();

        $allinvoicesshow = Invoice::where(function ($query) use ($futureDate) {
            $query->where('status', 'overdue')
                  ->orWhere(function ($q) use ($futureDate) {
                      $q->where('status', 'pending')
                        ->whereDate('invoice_date', '<=', $futureDate);
                  });
        })
        ->select('*', DB::raw("DATEDIFF(invoice_date, CURDATE()) as days_remaining"))
        ->orderByRaw("CASE status 
                        WHEN 'overdue' THEN 1 
                        WHEN 'pending' THEN 2 
                        WHEN 'paid' THEN 3 
                        ELSE 4 
                     END")
        ->orderBy('invoice_date', 'ASC')
        ->get();

        return view('index', [
            'allbuildings' => $allbuildings,
            'alllands' => $alllands,
            'activeContractsCount' => $activeContractsCount,
            'expiringContracts' => $expiringContracts,
            'allinvoicesshow' => $allinvoicesshow,
            'currentAmountPaid' => $currentAmountPaid,
            'currentAmountDue' => $currentAmountDue,
            'currentExpenses' => $currentExpenses,
            'filter' => $filter,
        ]);
    }
}