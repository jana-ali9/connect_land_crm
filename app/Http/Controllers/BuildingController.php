<?php

namespace App\Http\Controllers;

use App\Models\Building;
use App\Models\Permission;
use Illuminate\Http\Request;

class BuildingController extends Controller
{
    public function index(Request $request)
    {
        $query = Building::query();

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where('name', 'LIKE', "%$search%")
                ->orWhere('description', 'LIKE', "%$search%");
        }
        $query->orderBy('is_payed', 'asc');

        $allBuildings = $query->paginate(9)->appends(['search' => $request->search]);

        return view('buildings.index', compact('allBuildings'));
    }
    public function create()
    {
        return view('buildings.create');
    }
    public function store()
{
    // Normalize country to ISO-2 BEFORE validation (handles "Lebanon" -> "LB")
    $incomingCountry = trim((string) request('country'));
    if ($incomingCountry !== '') {
        $nameToIso = [
            'Lebanon'             => 'LB',
            'United States'       => 'US',
            'United Kingdom'      => 'GB',
            'Canada'              => 'CA',
            'United Arab Emirates'=> 'AE',
            'Saudi Arabia'        => 'SA',
            'Egypt'               => 'EG',
        ];
        if (strlen($incomingCountry) !== 2) {
            $incomingCountry = $nameToIso[$incomingCountry] ?? null; // unknown -> null
        } else {
            $incomingCountry = strtoupper($incomingCountry);
        }
        request()->merge(['country' => $incomingCountry]);
    }

    $validated = request()->validate([
        'name'        => ['required', 'min:3'],
        'description' => ['nullable'],
        'address'     => ['nullable', 'min:3'],
        'location'    => ['required', 'min:3'],
        'country'     => ['nullable', 'string', 'size:2'],
        'lat'         => ['nullable', 'numeric', 'between:-90,90'],
        'lng'         => ['nullable', 'numeric', 'between:-180,180'],
        'image'       => ['required', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:2048'],
    ]);

    // Move uploaded file -> image_path
    if (request()->hasFile('image')) {
        $validated['image_path'] = request()->file('image')->store('buildings', 'public');
        unset($validated['image']); // don't try to save UploadedFile
    }

    // Persist (make sure your Building model has the fillable keys)
    \App\Models\Building::create($validated);

    session()->flash('success', 'Building added successfully.');
    return to_route('buildings.index');
}
public function show(Building $building)
    {
        // eager-load units for this building
        $building->load(['units' => function ($q) {
            $q->orderBy('name');
        }]);

        return view('buildings.show', compact('building'));
    }

    public function edit(Building $building)
    {
        return view('buildings.edit', compact('building'));
    }

    public function sale(Building $building, Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0',
        ]);

        // تحديث بيانات الـ Unit
        $building->update([
            "end_price" => $request['amount'],
            "is_payed" => true,
        ]);
        foreach ($building->units as $unit) {
            // البحث عن العقد النشط
            $activeContract = $unit->contract()->where('contract_status', 'active')->first();

            if ($activeContract) {
                // تنفيذ الإلغاء باستخدام الروت
                return redirect()->route('contracts.cancellation', ['contract' => $activeContract->id]);
            }
        }

        return redirect()->back()->with('building', 'Unit sold successfully.');
    }
    public function update(Building $building)
    {

        request()->validate([
            "name" => ['required', 'min:3'],
            "description" => ['nullable'],
            "location" => ['required', 'min:3'],
            "image" => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'country' => 'nullable|string|size:2',
            'lat'     => 'required|numeric|between:-90,90',
            'lng'     => 'required|numeric|between:-180,180',
        ]);

        $data = request()->all();
        if (request()->hasFile('image') && request()->file('image')->isValid()) {
            if ($building->image_path) {
                $oldImagePath = public_path('storage/' . $building->image_path);
                if (file_exists($oldImagePath)) {
                    unlink($oldImagePath);
                }
            }
            $imagePath = request()->file('image')->store('Project', 'public');
        }
        $building->update([
            "name" => $data['name'],
            "description" => $data['description'],
            "location" => $data['location'],
            "image_path" => isset($imagePath) ? $imagePath : $building->image_path,

        ]);

        session()->flash('success', "We updated building named $building->name");

        return to_route("buildings.index");
    }
    public function destroy(Building $building)
    {
        if ($building->image_path) {
            $imagePath = public_path('storage/' . $building->image_path);
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
        }
        $building->delete();
        session()->flash('success', "Building '{$building->name}' has been deleted.");

        return to_route("buildings.index");
    }
}
