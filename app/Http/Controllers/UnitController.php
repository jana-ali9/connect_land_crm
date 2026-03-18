<?php

namespace App\Http\Controllers;

use App\Models\Building;
use App\Models\Unit;
use App\Models\UnitFeature;
use Illuminate\Http\Request;

class UnitController extends Controller
{
    public function index(Request $request)
    {
        $query = Unit::query();

        // البحث بالاسم أو الوصف
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%$search%")
                    ->orWhere('description', 'LIKE', "%$search%");
            });
        }

        // تصفية حسب المبنى (باستخدام has بدل filled)
        if ($request->filled('building_id')) {
            $query->where('building_id', $request->building_id);
        }

        // ترتيب حسب is_payed = false أولًا
        $query->orderBy('is_payed', 'asc'); // false هي الأول لأن false = 0 و true = 1

        // إرجاع النتائج مع الحفاظ على قيم البحث
        $allunits = $query->paginate(9)->appends([
            'search' => $request->search,
            'building_id' => $request->building_id
        ]);

        // جلب قائمة المباني لعرضها في الفلتر
        $buildings = Building::all();

        return view('units.index', compact('allunits', 'buildings'));
    }



    public function search(Request $request)
    {
        $search = $request->q;
        $units = Unit::where('is_rented', false)
            ->where(function ($query) use ($search) {
                $query->where('name', 'LIKE', "%$search%")
                    ->orWhere('description', 'LIKE', "%$search%");
            })
            ->get();
        return response()->json($units);
    }

    public function create()
    {
        $allbuildings = Building::all();
        return view('units.create', compact('allbuildings'));
    }
    public function store()
    {
        request()->validate([
            "name" => ['required', 'min:3'],
            "description" => ['required', 'min:9'],
            "image" => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            "area" => ['required', 'numeric'],
            'building_id' => 'required',
            'start_price' => ['nullable', 'numeric']
        ]);

        if (request()->hasFile('image')) {
            $imagePath = request()->file('image')->store('units', 'public');
        }
        $data = request()->all();
        Unit::create([
            "name" => $data['name'],
            "description" => $data['description'],
            "building_id" => $data['building_id'],
            "area" => $data['area'],
            "start_price" => $data['start_price'] ?? null,
            "image_path" => $imagePath ?? null,
        ]);
        session()->flash('success', "unit addes success");
        return to_route("units.index");
    }
    public function edit(Unit $unit)
    {
        $allbuildings = Building::all();
        $allunitfeatures = $unit->features;
        return view('units.edit', compact('unit', 'allbuildings', 'allunitfeatures'));
    }
    public function update(Unit $unit)
    {

        request()->validate([
            "name" => ['required', 'min:3'],
            "description" => ['required', 'min:9'],
            "image" => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            "area" => ['required'],
            'building_id' => 'required',
            'start_price' => ['nullable', 'numeric']
        ]);

        $data = request()->all();

        if (request()->hasFile('image') && request()->file('image')->isValid()) {
            if ($unit->image_path) {
                $oldImagePath = public_path('storage/' . $unit->image_path);
                if (file_exists($oldImagePath)) {
                    unlink($oldImagePath);
                }
            }
            $imagePath = request()->file('image')->store('units', 'public');
        }
        $unit->update([
            "name" => $data['name'],
            "description" => $data['description'],
            "building_id" => $data['building_id'],
            "area" => $data['area'],
            "start_price" => $data['start_price'] ?? null,
            "image_path" => isset($imagePath) ? $imagePath : $unit->image_path,
        ]);

        session()->flash('success', "We updated unit named $unit->name");

        return to_route("units.index");
    }

    public function show(Unit $unit)
    {
        // Eager-load the building to read name, photo, and lat/lng
        $unit->load('building');

        // Optional: simple 404 guard if no building linked
        if (!$unit->building) {
            abort(404, 'This unit is not attached to any building.');
        }

        return view('units.show', [
            'unit'     => $unit,
            'building' => $unit->building,
        ]);
    }


    public function sale(Unit $unit, Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0',
        ]);

        // تحديث بيانات الـ Unit
        $unit->update([
            "end_price" => $request['amount'],
            "is_payed" => true,
        ]);

        // البحث عن العقد النشط
        $activeContract = $unit->contract()->where('contract_status', 'active')->first();

        if ($activeContract) {
            // تنفيذ الإلغاء باستخدام الروت
            return redirect()->route('contracts.cancellation', ['contract' => $activeContract->id]);
        }

        return redirect()->back()->with('success', 'Unit sold successfully.');
    }


    public function destroy(Unit $unit)
    {
        if ($unit->image_path) {
            $imagePath = public_path('storage/' . $unit->image_path);
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
        }
        $unit->delete();
        session()->flash('success', "unit '{$unit->name}' has been deleted.");

        return to_route("units.index");
    }
}
