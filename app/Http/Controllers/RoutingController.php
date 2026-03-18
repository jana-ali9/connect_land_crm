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

        switch ($filter) {
            case 'week':
                $startDate = $startOfWeek;
                break;
            case 'year':
                $startDate = $startOfYear;
                break;
            case 'month':
                $startDate = $startOfMonth;
                break;
            case 'all':
            default:
                $startDate = null;
                break;
        }

        // Payments
        $filteredPayments = PaymentHistory::query();
        if ($startDate) {
            $filteredPayments->where('created_at', '>=', $startDate);
        }
        $currentAmountPaid = $filteredPayments->sum('amount_paid');

        // Invoices
        $filteredInvoices = Invoice::query();
        if ($startDate) {
            $filteredInvoices->where('invoice_date', '>=', $startDate);
        }
        $currentAmountDue = $filteredInvoices->sum(DB::raw('amount_due + services_cost - amount_paid'));

        // Expenses
        $filteredExpenses = UnitExpense::query();
        if ($startDate) {
            $filteredExpenses->where('created_at', '>=', $startDate);
        }
        $currentExpenses = $filteredExpenses->sum('amount');

        // Static data
        $allbuildings = Building::all();
        $alllands = Land::all();

        $activeContractsCount = Contract::where('contract_status', 'active')
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