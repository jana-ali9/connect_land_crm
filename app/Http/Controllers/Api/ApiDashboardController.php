<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Building;
use App\Models\Unit;
use App\Models\Land;
use App\Models\Client;     // if you have
use App\Models\Contract;   // if you have
use App\Models\Invoice;    // if you have
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ApiDashboardController extends Controller
{
    public function summary(Request $req)
    {
        $filter = $req->get('filter', 'all');
$from   = $req->get('from_date');
$to     = $req->get('to_date');
        $totals = [
            'buildings'        => Building::count(),
            'units'            => Unit::count(),
            'lands'            => Land::count(),
            'clients'          => class_exists(Client::class)   ? Client::count()   : 0,
            'active_contracts' => class_exists(Contract::class) ? Contract::where('contract_status','active')->count() : 0,
        ];

        $occTotal = $totals['units'];
        $occOcc   = Unit::where(function($q){
                          $q->where('is_rented',1)
                            ->orWhere('status','rented');
                      })->count();
        $occupancy = [
            'total_units'    => $occTotal,
            'occupied_units' => $occOcc,
            'rate'           => $occTotal ? round(($occOcc / $occTotal) * 100) : 0,
        ];

        $invCounts = ['paid'=>0,'pending'=>0,'overdue'=>0,'suspended'=>0];
        $invTotals = ['paid'=>0,'outstanding'=>0];

        if (class_exists(Invoice::class)) {
            $invCounts = Invoice::select('status', DB::raw('COUNT(*) as c'))
                        ->groupBy('status')->pluck('c','status')->all() + $invCounts;

            $paid = (float) Invoice::where('status','paid')->sum(DB::raw('COALESCE(amount_paid, 0)'));
            $due  = (float) Invoice::whereIn('status',['pending','overdue','suspended'])
                        ->sum(DB::raw('COALESCE(amount_due, 0) - COALESCE(amount_paid, 0)'));
            $invTotals = ['paid'=>$paid, 'outstanding'=>max($due,0)];
        }

        // last 12 months revenue
        $revenue = [];
        $start = Carbon::now()->startOfMonth()->subMonths(11);
        for ($i=0; $i<12; $i++) {
            $m = (clone $start)->addMonths($i);
            $revenue[] = [
                'month'    => $m->format('Y-m'),
                'paid'     => class_exists(Invoice::class)
                                ? (float) Invoice::where('status','paid')
                                    ->whereBetween('invoice_date', [$m->copy()->startOfMonth(), $m->copy()->endOfMonth()])
                                    ->sum(DB::raw('COALESCE(amount_paid,0)'))
                                : 0,
                'invoices' => class_exists(Invoice::class)
                                ? (int) Invoice::whereBetween('invoice_date', [$m->copy()->startOfMonth(), $m->copy()->endOfMonth()])->count()
                                : 0,
            ];
        }

        // top buildings by occupied units
        $topBuildings = Building::query()
            ->withCount([
                'units as total_units',
                'units as occupied_units' => fn($u) => $u->where(function($x){
                    $x->where('status','rented')->orWhere('is_rented',1);
                })
            ])
            ->orderByDesc('occupied_units')
            ->take(5)
            ->get()
            ->map(fn($b) => [
                'id'              => $b->id,
                'name'            => (string) $b->name,
                'total_units'     => (int) ($b->total_units ?? 0),
                'occupied_units'  => (int) ($b->occupied_units ?? 0),
                'occupancy_rate'  => ($b->total_units ?? 0) ? round(($b->occupied_units / max($b->total_units,1)) * 100) : 0,
            ])
            ->values();

        return response()->json([
            'ok'   => true,
            'data' => [
                'totals'              => $totals,
                'occupancy'           => $occupancy,
                'invoices'            => ['counts' => $invCounts, 'totals' => $invTotals],
                'revenueLast12Months' => $revenue,
                'topBuildings'        => $topBuildings,
            ],
        ]);
    }
    private function applyDateFilter($query, $filter, $from = null, $to = null)
{
    switch ($filter) {
        case 'this_year':
            return $query->whereYear('created_at', now()->year);

        case 'this_month':
            return $query->whereMonth('created_at', now()->month);

        case 'this_week':
            return $query->whereBetween('created_at', [
                now()->startOfWeek(),
                now()->endOfWeek()
            ]);

        case 'custom':
            if ($from && $to) {
                return $query->whereBetween('created_at', [$from, $to]);
            }
            return $query;

        default:
            return $query; // all time
    }
}
}
