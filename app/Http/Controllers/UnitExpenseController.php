<?php

namespace App\Http\Controllers;

use App\Models\Building;
use App\Models\ExpenseOffer;
use App\Models\Unit;
use App\Models\UnitExpense;
use Illuminate\Http\Request;

class UnitExpenseController extends Controller
{

    /**
     * Show the form for creating a new resource.
     */


    public function getOffers($unitId)
    {
        $offers = ExpenseOffer::where('expense_id', $unitId)->get();

        return response()->json($offers);
    }

    /**
     * Store a newly created resource in storage.
     */
    // public function store(Request $request)
    // {
    //     $request->validate([
    //         'amount' => 'required|numeric|min:0',
    //         'allocation_type' => 'required|in:unit,building,land',
    //         'target_id' => 'required|numeric',
    //         'expense_name' => 'required|string|max:255',
    //         'description' => 'nullable|string',
    //         'category_id' => 'nullable|numeric|min:0',
    //     ]);
    //     //'amount' =>  $request->amount,
    //     if ($request->allocation_type === "unit") {
    //         $unitselect = Unit::find($request->target_id);
    //         UnitExpense::create([
    //             'expense_name' => $request->expense_name,
    //             'unit_id' => $request->target_id,
    //             'building_id' => $unitselect->building_id,
    //             'amount' => $request->amount,
    //             'allocation_type' => $request->allocation_type,
    //             'category_id' => $request->category_id,
    //             'description' => $request->description,
    //         ]);
    //     } elseif ($request->allocation_type === "building") {
    //         UnitExpense::create([
    //             'expense_name' => $request->expense_name,
    //             'building_id' => $request->target_id,
    //             'amount' => $request->amount,
    //             'allocation_type' => $request->allocation_type,
    //             'category_id' => $request->category_id,
    //             'description' => $request->description,
    //         ]);

    //     } elseif ($request->allocation_type === "land") {
    //         // handle land expense, e.g.
    //         LandExpense::create([
    //             'expense_name' => $request->expense_name,
    //             'land_id' => $request->target_id,
    //             'amount' => $request->amount,
    //             'allocation_type' => $request->allocation_type,
    //             'category_id' => $request->category_id,
    //             'description' => $request->description,
    //         ]);
    //     }


    //     return redirect()->back()->with('success', 'Expense added successfully.');
    // }
    public function store(Request $request)
{
    $request->validate([
        'amount' => 'required|numeric|min:0',
        'allocation_type' => 'required|in:unit,building,land',
        'target_id' => 'required|numeric',
        'expense_name' => 'required|string|max:255',
        'description' => 'nullable|string',
        'category_id' => 'nullable|numeric|min:0',
    ]);

    if ($request->allocation_type === "unit") {
        $unit = Unit::findOrFail($request->target_id);
        UnitExpense::create([
            'expense_name' => $request->expense_name,
            'unit_id' => $unit->id,
            'building_id' => $unit->building_id,
            'amount' => $request->amount,
            'allocation_type' => $request->allocation_type,
            'category_id' => $request->category_id,
            'description' => $request->description,
        ]);
    } elseif ($request->allocation_type === "building") {
        UnitExpense::create([
            'expense_name' => $request->expense_name,
            'building_id' => $request->target_id,
            'amount' => $request->amount,
            'allocation_type' => $request->allocation_type,
            'category_id' => $request->category_id,
            'description' => $request->description,
        ]);
    } elseif ($request->allocation_type === "land") {
        UnitExpense::create([
            'expense_name' => $request->expense_name,
            'land_id' => $request->target_id,
            'amount' => $request->amount,
            'allocation_type' => $request->allocation_type,
            'category_id' => $request->category_id,
            'description' => $request->description,
        ]);
    }

    return redirect()->back()->with('success', 'Expense added successfully.');
}


    // public function storeInPage(Request $request)
    // {
    //     $request->validate([
    //         'unit_id' => 'nullable|exists:units,id',
    //         'building_id' => 'required|exists:buildings,id',
    //         'expense_name' => 'required|string|max:255',
    //         'description' => 'nullable|string',
    //         'amount' => 'required|numeric|min:0',
    //         'category_id' => 'nullable|numeric|min:0',

    //     ]);
    //     if ($request->unit_id) {
    //         UnitExpense::create([
    //             'expense_name' => $request->expense_name,
    //             'building_id' => $request->building_id,
    //             'unit_id' => $request->unit_id,
    //             'amount' => $request->amount,
    //             'allocation_type' => 'unit',
    //             'description' => $request->description,
    //             'category_id' => $request->category_id,
    //         ]);
    //     } else {
    //         UnitExpense::create([
    //             'expense_name' => $request->expense_name,
    //             'building_id' => $request->building_id,
    //             'amount' => $request->amount,
    //             'allocation_type' => 'building',
    //             'description' => $request->description,
    //             'category_id' => $request->category_id,
    //         ]);
    //     }

    //     return to_route('expenseOffers.index');
    // }
    /**
     * Display the specified resource.
     */
    public function storeInPage(Request $request)
{
    $request->validate([
        'allocation_type' => 'required|in:unit,building,land',
        'target_id' => 'required|numeric',
        'expense_name' => 'required|string|max:255',
        'description' => 'nullable|string',
        'amount' => 'required|numeric|min:0',
        'category_id' => 'nullable|numeric|min:0',
    ]);

    $data = [
        'expense_name' => $request->expense_name,
        'amount' => $request->amount,
        'allocation_type' => $request->allocation_type,
        'category_id' => $request->category_id,
        'description' => $request->description,
    ];

    if ($request->allocation_type === 'unit') {
        $unit = Unit::findOrFail($request->target_id);
        $data['unit_id'] = $unit->id;
        $data['building_id'] = $unit->building_id;
    } elseif ($request->allocation_type === 'building') {
        $data['building_id'] = $request->target_id;
    } elseif ($request->allocation_type === 'land') {
        $data['land_id'] = $request->target_id;
    }

    UnitExpense::create($data);

    return to_route('expenseOffers.index')->with('success', 'Expense added successfully.');
}

    public function show(UnitExpense $unitExpense)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(UnitExpense $unitExpense)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, UnitExpense $unitExpense)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(UnitExpense $unitExpense)
    {
        $unitExpense->delete();
        return redirect()->back();
    }
}
