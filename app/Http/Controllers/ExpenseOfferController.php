<?php

namespace App\Http\Controllers;

use App\Models\Building;
use App\Models\ExpenseCategory;
use App\Models\ExpenseOffer;
use App\Models\UnitExpense;
use Illuminate\Http\Request;
use App\Models\Land;


class ExpenseOfferController extends Controller
{
    public function index(Request $request)
    {
        $query = UnitExpense::with('expenseCategory'); // مهم علشان تظهر العلاقات

        if ($request->filled('building_id')) {
            $query->where('building_id', $request->building_id);
        }

        if ($request->filled('unit_id')) {
            $query->where('unit_id', $request->unit_id);
        }
        if ($request->filled('land_id')) {
            $query->where('land_id', $request->land_id);
        }

        if ($request->filled('min_amount')) {
            $query->where('amount', '>=', $request->min_amount);
        }

        if ($request->filled('max_amount')) {
            $query->where('amount', '<=', $request->max_amount);
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        $allUnits = $query->paginate(10);
        $buildings = Building::all();
        $lands = Land::all();
        $categories = ExpenseCategory::all(); // ✅ استدعاء الفئات

        return view('expense_offers.index', compact('allUnits', 'buildings', 'categories', 'lands'));
    }


    public function search(Request $request)
    {
        $search = $request->q;
        $offers = ExpenseOffer::where('company_name', 'LIKE', "%$search%")
            ->orWhere('offer_amount', 'LIKE', "%$search%")
            ->get();

        return response()->json($offers);
    }

    public function updateStatus(Request $request, ExpenseOffer $expenseOffer)
    {
        $request->validate([
            'status' => 'boolean',
        ]);

        if ($request->status) {
            // ✅ نخلي العرض ده فقط هو المقبول
            $expenseOffer->expense->expenseOffers()->update(['status' => 0]);

            // ✅ تحديث حالة العرض الحالي
            $expenseOffer->update(['status' => $request->status]);

            // ✅ تحديث الـ amount في الجدول الأساسي
            $expenseOffer->expense->updateAmountFromAcceptedOffer();
        }

        return response()->json(['success' => true]);
    }





        public function create()
{
    $buildings = Building::with('units')->get(); // if used for dynamic unit selection
    $categories = ExpenseCategory::all();        // if you use categories
    $lands = Land::all();                        // 👈 this is the missing variable

    return view('expense_offers.create', compact('buildings', 'categories', 'lands'));
}

    public function store(Request $request)
    {
        $request->validate([
            'expense_id' => 'required|exists:unit_expenses,id',
            'company_name' => 'required|string|max:255',
            'offer_amount' => 'required|numeric|min:0',
            'expiry_date' => 'nullable|date',
            'description' => 'nullable|string',
        ]);

        ExpenseOffer::create($request->all());

        return response()->json(['success' => true]);
    }


    public function store1(Request $request)
    {
        $request->validate([
            'expense_id' => 'required|exists:unit_expenses,id',
            'company_name' => 'required|string|max:255',
            'offer_amount' => 'required|numeric|min:0',
            'description' => 'nullable|string',
        ]);

        $offer = ExpenseOffer::create([
            'expense_id' => $request->expense_id,
            'company_name' => $request->company_name,
            'offer_amount' => $request->offer_amount,
            'description' => $request->description,
        ]);

        return response()->json([
            'success' => true,
            'offer_id' => $offer->id
        ]);
    }

    public function edit(ExpenseOffer $expenseOffer)
    {
        $expenses = UnitExpense::all();
        return view('expense_offers.edit', compact('expenseOffer', 'expenses'));
    }

    public function update(ExpenseOffer $expenseOffer)
    {
        request()->validate([
            'company_name' => 'required|string|max:255',
            'offer_amount' => 'required|numeric|min:0',
            'description' => 'nullable|string',
        ]);

        $data = request()->all();

        $expenseOffer->update([
            'company_name' => $data['company_name'],
            'offer_amount' => $data['offer_amount'],
            'description' => $data['description'],
        ]);

        session()->flash('success', "تم تحديث العرض بنجاح");
        return to_route('expense-offers.index');
    }

    public function destroy(ExpenseOffer $expenseOffer)
    {
        $expenseOffer->delete();
        return redirect()->back()->with('success', 'Expense offer deleted successfully.');
    }
}
