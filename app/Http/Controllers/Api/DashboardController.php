<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function summary(Request $request)
    {
        // ---- Permission (same idea you had)
        $user = $request->user();
        if (!$user || !$this->userCan($user->id, 'read dashboard')) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $today        = Carbon::today();
        $startOfWeek  = $today->copy()->startOfWeek();
        $startOfMonth = $today->copy()->startOfMonth();
        $startOfYear  = $today->copy()->startOfYear();
        $futureDate   = $today->copy()->addDays(10);   // like Blade – near-term invoices
        $expiringDate = $today->copy()->addDays(30);   // contracts ending in 30 days
        $start12      = $today->copy()->startOfMonth()->subMonths(11);

        // ---- Time filter (week|month|year|all)
        $filter = $request->query('filter', 'all');
        $startDate = match ($filter) {
            'week'  => $startOfWeek,
            'month' => $startOfMonth,
            'year'  => $startOfYear,
            default => null,
        };

        // ==================== KPI-like numbers (same as Blade) ====================
        // Payments
        $paymentsQ = DB::table('payment_histories');
        if ($startDate) {
            $paymentsQ->where('created_at', '>=', $startDate);
        }
        $currentAmountPaid = (float) $paymentsQ->sum('amount_paid');

        // Invoices – amount due in filter window (services_cost included)
        $invoicesQ = DB::table('invoices');
        if ($startDate) {
            $invoicesQ->where('invoice_date', '>=', $startDate);
        }
        $currentAmountDue = (float) $invoicesQ
            ->selectRaw('SUM(amount_due + COALESCE(services_cost,0) - COALESCE(amount_paid,0)) as s')
            ->value('s');

        // Expenses
        $expensesQ = DB::table('unit_expenses');
        if ($startDate) {
            $expensesQ->where('created_at', '>=', $startDate);
        }
        $currentExpenses = (float) $expensesQ->sum('amount');

        // ==================== Totals & occupancy ====================
        $totals = [
            'buildings' => (int) DB::table('buildings')->count(),
            'units'     => (int) DB::table('units')->count(),
            'lands'     => (int) DB::table('lands')->count(),
            'clients'   => (int) DB::table('clients')->count(),
        ];

        $occupiedUnits = (int) DB::table('units')->where('is_rented', 1)->count();
        $occupancyRate = $totals['units'] > 0
            ? round(($occupiedUnits / $totals['units']) * 100, 1)
            : 0.0;

        // Active contracts – like Blade
        $activeContractsCount = (int) DB::table('contracts')
            ->where('contract_status', 'active')
            ->whereDate('end_date', '>=', $today)
            ->count();

        // ==================== Invoice stats (global) ====================
        $invoiceCounts = DB::table('invoices')
            ->selectRaw('status, COUNT(*) as cnt')
            ->groupBy('status')
            ->pluck('cnt', 'status');

        $totalsPaid = (float) DB::table('invoices')->sum('amount_paid');

        $outstanding = (float) DB::table('invoices')
            ->selectRaw('SUM(GREATEST(amount_due + COALESCE(services_cost,0) - COALESCE(amount_paid,0), 0)) as s')
            ->value('s');

        // ==================== Revenue trend (last 12 months) ====================
        $trendRows = DB::table('invoices')
            ->whereDate('invoice_date', '>=', $start12->toDateString())
            ->selectRaw("DATE_FORMAT(invoice_date, '%Y-%m') as ym, SUM(amount_paid) as paid, COUNT(*) as invoices")
            ->groupBy('ym')
            ->orderBy('ym')
            ->get();

        $months = [];
        for ($i = 0; $i < 12; $i++) {
            $m = $start12->copy()->addMonths($i)->format('Y-m');
            $months[$m] = ['month' => $m, 'paid' => 0.0, 'invoices' => 0];
        }
        foreach ($trendRows as $r) {
            $months[$r->ym] = [
                'month'    => $r->ym,
                'paid'     => (float) $r->paid,
                'invoices' => (int) $r->invoices,
            ];
        }
        $revenueLast12 = array_values($months);

        // ==================== Top buildings (by occupancy) ====================
        $topBuildings = DB::table('buildings as b')
            ->leftJoin('units as u', 'u.building_id', '=', 'b.id')
            ->selectRaw("
                b.id, b.name,
                COUNT(u.id) as total_units,
                SUM(CASE WHEN u.is_rented = 1 THEN 1 ELSE 0 END) as occupied_units
            ")
            ->groupBy('b.id', 'b.name')
            ->get()
            ->map(function ($row) {
                $row->total_units    = (int) $row->total_units;
                $row->occupied_units = (int) $row->occupied_units;
                $row->occupancy_rate = $row->total_units > 0
                    ? round(($row->occupied_units / $row->total_units) * 100, 1)
                    : 0.0;
                return $row;
            })
            ->sortByDesc('occupancy_rate')
            ->take(5)
            ->values();

        // ==================== Contracts ending in next 30 days ====================
        $upcomingRenewals = DB::table('contracts as c')
            ->leftJoin('clients as cl', 'cl.id', '=', 'c.client_id')
            ->leftJoin('units as u', 'u.id', '=', 'c.unit_id')
            ->leftJoin('buildings as b', 'b.id', '=', 'c.building_id')
            ->where('c.contract_status', 'active')
            ->whereBetween('c.end_date', [$today, $expiringDate])
            ->selectRaw("
                c.id,
                c.end_date,
                c.base_rent,
                c.contract_status,
                DATEDIFF(c.end_date, CURRENT_DATE) as days_remaining,
                cl.name as client,
                u.name  as unit,
                b.name  as building
            ")
            ->orderBy('c.end_date', 'ASC')
            ->limit(10)
            ->get();

        // ==================== Recent invoices (overdue or near-due) ====================
        // Same selection as Blade:
        //  - overdue
        //  - or pending with invoice_date <= today+10
        // Also compute days_remaining and prioritize overdue first.
        $recentInvoices = DB::table('invoices as i')
            ->leftJoin('clients as cl', 'cl.id', '=', 'i.client_id')
            ->where(function ($q) use ($futureDate) {
                $q->where('i.status', 'overdue')
                  ->orWhere(function ($q2) use ($futureDate) {
                      $q2->where('i.status', 'pending')
                         ->whereDate('i.invoice_date', '<=', $futureDate);
                  });
            })
            ->selectRaw("
                i.id,
                i.invoice_date,
                i.amount_due,
                COALESCE(i.services_cost,0) as services_cost,
                COALESCE(i.amount_paid,0)  as amount_paid,
                i.status,
                i.type,
                DATEDIFF(i.invoice_date, CURRENT_DATE) as days_remaining,
                cl.name as client
            ")
            ->orderByRaw("CASE i.status
                            WHEN 'overdue' THEN 1
                            WHEN 'pending' THEN 2
                            WHEN 'paid'    THEN 3
                            ELSE 4
                          END")
            ->orderBy('i.invoice_date', 'ASC')
            ->limit(50)
            ->get();

        // (Optional) recent payments – keep for completeness
        $recentPayments = DB::table('payment_histories as p')
            ->leftJoin('clients as cl', 'cl.id', '=', 'p.client_id')
            ->select('p.id', 'p.payment_date', 'p.amount_paid', 'p.due_after_payment', 'cl.name as client')
            ->orderByDesc('p.payment_date')
            ->limit(10)
            ->get();

        return response()->json([
            'ok' => true,
            'data' => [
                // same keys your Flutter reads
                'totals' => $totals + ['active_contracts' => $activeContractsCount],

                'occupancy' => [
                    'total_units'    => $totals['units'],
                    'occupied_units' => $occupiedUnits,
                    'rate'           => $occupancyRate,
                ],

                // for KPI cards on mobile
                'currentAmountPaid' => $currentAmountPaid,
                'currentAmountDue'  => $currentAmountDue,
                'currentExpenses'   => $currentExpenses,

                // invoice stats block
                'invoices' => [
                    'counts' => [
                        'paid'      => (int) ($invoiceCounts['paid'] ?? 0),
                        'pending'   => (int) ($invoiceCounts['pending'] ?? 0),
                        'overdue'   => (int) ($invoiceCounts['overdue'] ?? 0),
                        'suspended' => (int) ($invoiceCounts['suspended'] ?? 0),
                    ],
                    'totals' => [
                        'paid'        => $totalsPaid,
                        'outstanding' => $outstanding,
                    ],
                ],

                'revenueLast12Months' => $revenueLast12,
                'topBuildings'        => $topBuildings,

                // lists your Flutter dashboard renders
                'upcomingRenewals'    => $upcomingRenewals,   // contracts table
                'recentInvoices'      => $recentInvoices,     // invoices table
                'recentPayments'      => $recentPayments,     // optional
            ],
        ]);
    }

    /** Minimal permission check against your role/permission tables */
    private function userCan(int $userId, string $permName): bool
    {
        $roleId = DB::table('users')->where('id', $userId)->value('role_id');
        if (!$roleId) return false;

        return DB::table('permission_roles as pr')
            ->join('permissions as p', 'p.id', '=', 'pr.permission_id')
            ->where('pr.role_id', $roleId)
            ->where('p.name', $permName)
            ->exists();
    }
}
