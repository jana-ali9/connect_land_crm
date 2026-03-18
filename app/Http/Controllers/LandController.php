<?php

namespace App\Http\Controllers;

use App\Models\Land;
use Illuminate\Http\Request;

class LandController extends Controller
{
    public function index(Request $request)
    {
        $query = Land::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                    ->orWhere('location', 'like', "%$search%")
                    ->orWhere('property_number', 'like', "%$search%")
                    ->orWhere('district_zone', 'like', "%$search%");
            });
        }

        $allLands = $query->latest()->paginate(9);

        return view('lands.index', compact('allLands'));
    }

    public function create()
    {
        return view('lands.create');
    }

public function store(Request $request)
{
    $data = $request->validate([
        'name'            => 'required|string|max:255',
        'description'     => 'nullable|string',
        'location'        => 'required|string|max:255',
        'area'            => 'required|numeric',
        'property_number' => 'required|string|max:255',
        'section_number'  => 'required|string|max:255',
        'district_zone'   => 'required|string|max:255',
        'photo'           => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',

        // Map fields (match DB)
        'address' => 'nullable|string|max:255',
        'country' => 'nullable|string|size:2',   // ISO-2 like "LB"
        'lat'     => 'nullable|numeric|between:-90,90',
        'lng'     => 'nullable|numeric|between:-180,180',
    ]);

    // Normalize country to uppercase (e.g. lb -> LB)
    if (!empty($data['country'])) {
        $data['country'] = strtoupper($data['country']);
    }

    // Create land (everything except photo is in $data)
    $land = new \App\Models\Land($data);

    // Handle photo
    if ($request->hasFile('photo')) {
        $land->photo = $request->file('photo')->store('lands', 'public');
    }

    $land->save();

    return redirect()->route('lands.index')->with('success', 'Land created successfully.');
}



    public function edit(Land $land)
    {
        return view('lands.edit', compact('land'));
    }

    public function show(Land $land)
    {
        return view('lands.show', compact('land'));
    }


    public function update(Request $request, Land $land)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'location' => 'required|string|max:255',
            'area' => 'required|numeric',
            'property_number' => 'required|string|max:255',
            'section_number' => 'required|string|max:255',
            'district_zone' => 'required|string|max:255',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $land->fill($request->except('photo'));

        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('lands', 'public');
            $land->photo = $path;
        }

        $land->save();

        return redirect()->route('lands.index')->with('success', 'Land updated successfully.');
    }


    public function destroy(Land $land)
    {
        $land->delete();
        return redirect()->route('lands.index')->with('success', 'Land deleted successfully.');
    }
}
