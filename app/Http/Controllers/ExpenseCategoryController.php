<?php

namespace App\Http\Controllers;

use App\Models\ExpenseCategory;
use Illuminate\Http\Request;

class ExpenseCategoryController extends Controller
{

    public function fetch()
    {
        $categories = ExpenseCategory::select('id', 'category_name')->get();
        return response()->json($categories);
    }

    public function store(Request $request)
    {
        $request->validate([
            'category_name' => 'required|string|max:255',
        ]);

        $category = ExpenseCategory::create([
            'category_name' => $request->category_name,
        ]);

        return response()->json(['success' => true, 'category' => $category]);
    }

    public function destroy($id)
    {
        $category = ExpenseCategory::find($id);
        if ($category) {
            $category->delete();
            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false], 404);
    }
}
