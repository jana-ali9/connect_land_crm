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
        // 1. Permission Check
        $user = $request->user();
        if (!$user || !$this->userCan($user->id, 'read dashboard')) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        // 2. Dates Setup
        $today        = Carbon::today();
        $futureDate   = $today->copy()->addDays(10);
        $expiringDate = $today->copy()->addDays(30);
        $start12      = $today->copy()->startOfMonth()->subMonths(11);

        // 3. Filtering Logic
        $filter = $request->query('filter', 'all');
        $startDate = null;
        $endDate   = null;

        if ($filter === 'custom') {
            if ($request->has('start_date') && $request->has('end_date')) {
                $startDate = Carbon::parse($request->query('start_date'))->startOfDay();
                $endDate   = Carbon::parse($request->query('end_date'))->endOfDay();
            }
        } else {
            $startDate = match ($filter) {
                'week'  => $today->copy()->startOfWeek(),
                'month' => $today->copy()->startOfMonth(),
                'year'  => $today->copy()->startOfYear(),
                default => null,
            };
        }

        // Helper for consistent date filtering across all queries
        $applyDateFilter = function ($query, $column = 'created_at') use ($startDate, $endDate) {
            if ($startDate) {
                if ($endDate) {
                    $query->whereBetween($column, [$startDate, $endDate]);
                } else {
                    $query->where($column, '>=', $startDate);
                }
            }
            return $query;
        };

        // ==================== KPI Numbers (Payments, Invoices, Expenses) ====================
        $currentAmountPaid = (float) $applyDateFilter(DB::table('payment_histories'))->sum('amount_paid');
        
        $currentAmountDue = (float) $applyDateFilter(DB::table('invoices'), 'invoice_date')
            ->selectRaw('SUM(amount_due + COALESCE(services_cost,0) - COALESCE(amount_paid,0)) as s')
            ->value('s');

        $currentExpenses = (float) $applyDateFilter(DB::table('unit_expenses'))->sum('amount');

        // ==================== Totals & Occupancy ====================
     $buildingsCount = (int) $applyDateFilter(DB::table('buildings'))->count();
        $landsCount     = (int) $applyDateFilter(DB::table('lands'))->count();
        $clientsCount   = (int) $applyDateFilter(DB::table('clients'))->count();
        
        // الوحدات المضافة في هذا التاريخ
        $unitsQuery     = $applyDateFilter(DB::table('units'));
        $unitsTotal     = (int) (clone $unitsQuery)->count();
        
        // العقود التي بدأت في هذا التاريخ (بدل created_at للعقود)
        $activeContractsCount = (int) $applyDateFilter(
            DB::table('contracts')->where('contract_status', 'active'), 
            'start_date' // الفلترة هنا بناءً على تاريخ بداية العقد
        )->count();

        // حساب نسبة الإشغال للوحدات "التي تأثرت بالفلتر"
        $occupiedUnits  = (int) (clone $unitsQuery)->where('is_rented', 1)->count();
        $occupancyRate  = $unitsTotal > 0 ? round(($occupiedUnits / $unitsTotal) * 100, 1) : 0.0;

        $activeContractsCount = (int) $applyDateFilter(
            DB::table('contracts')->where('contract_status', 'active')->whereDate('end_date', '>=', $today)
        )->count();

        // ==================== Invoice Stats (Filtered) ====================
        $invStatsQ = $applyDateFilter(DB::table('invoices'), 'invoice_date');
        
        $invoiceCounts = (clone $invStatsQ)->selectRaw('status, COUNT(*) as cnt')->groupBy('status')->pluck('cnt', 'status');
        $totalsPaid    = (float) (clone $invStatsQ)->sum('amount_paid');
        $outstanding   = (float) (clone $invStatsQ)
            ->selectRaw('SUM(GREATEST(amount_due + COALESCE(services_cost,0) - COALESCE(amount_paid,0), 0)) as s')
            ->value('s');

        // ==================== Tables (with "No Result" Logic) ====================
        // Recent Invoices filtered by date
        $recentInvoices = $applyDateFilter(DB::table('invoices as i'), 'i.invoice_date')
            ->leftJoin('clients as cl', 'cl.id', '=', 'i.client_id')
            ->where(fn($q) => $q->where('i.status', 'overdue')->orWhere(fn($q2) => $q2->where('i.status', 'pending')->whereDate('i.invoice_date', '<=', $futureDate)))
            ->selectRaw("i.*, cl.name as client, DATEDIFF(i.invoice_date, CURRENT_DATE) as days_remaining")
            ->orderByRaw("CASE i.status WHEN 'overdue' THEN 1 WHEN 'pending' THEN 2 ELSE 3 END")
            ->limit(10)->get();

        // Top Buildings
        $topBuildings = DB::table('buildings as b')
            ->leftJoin('units as u', 'u.building_id', '=', 'b.id')
            ->selectRaw("b.id, b.name, COUNT(u.id) as total_units, SUM(CASE WHEN u.is_rented = 1 THEN 1 ELSE 0 END) as occupied_units")
            ->groupBy('b.id', 'b.name')->orderByRaw("(occupied_units/total_units) DESC")->limit(5)->get();

        // Revenue Trend (12 Months)
        $trendRows = DB::table('invoices')->whereDate('invoice_date', '>=', $start12->toDateString())
            ->selectRaw("DATE_FORMAT(invoice_date, '%Y-%m') as ym, SUM(amount_paid) as paid, COUNT(*) as invoices")
            ->groupBy('ym')->orderBy('ym')->get();
$hasData = ($buildingsCount > 0 || $currentAmountPaid > 0 || $unitsTotal > 0);
        // Final Response
  return response()->json([
            'ok' => true,
            'has_results' => $hasData,
            'data' => [
                'totals' => [
                    // إذا لم يوجد نتائج في التاريخ المحدد، ستظهر الأرقام 0 تلقائياً
                    'buildings' => $buildingsCount, 
                    'units'     => $unitsTotal,
                    'lands'     => $landsCount,
                    'clients'   => $clientsCount,
                    'active_contracts' => $activeContractsCount,
                ],
                'occupancy' => [
                    'total_units'    => $unitsTotal,
                    'occupied_units' => $occupiedUnits,
                    'rate'           => $occupancyRate,
                ],
                'currentAmountPaid' => $currentAmountPaid,
                'currentAmountDue'  => $currentAmountDue,
                'currentExpenses'   => $currentExpenses,
                'invoices' => [
                    'counts' => [
                        'paid'      => (int) ($invoiceCounts['paid'] ?? 0),
                        'pending'   => (int) ($invoiceCounts['pending'] ?? 0),
                        'overdue'   => (int) ($invoiceCounts['overdue'] ?? 0),
                    ],
                    'totals' => ['paid' => $totalsPaid, 'outstanding' => $outstanding],
                ],
                'recentInvoices' => $recentInvoices->isEmpty() ? [] : $recentInvoices,
                'topBuildings'   => $topBuildings,
                'revenueLast12Months' => $trendRows,
            ],
        ]);
    }

    private function userCan(int $userId, string $permName): bool
    {
        $roleId = DB::table('users')->where('id', $userId)->value('role_id');
        return DB::table('permission_roles as pr')
            ->join('permissions as p', 'p.id', '=', 'pr.permission_id')
            ->where('pr.role_id', $roleId)->where('p.name', $permName)->exists();
    }
}