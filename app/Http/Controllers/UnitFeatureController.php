<?php

namespace App\Http\Controllers;

use App\Models\UnitFeature;
use Illuminate\Http\Request;

class UnitFeatureController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name.*' => 'required|string|max:255',
            'description.*' => 'nullable|string',
            'image.*' => 'nullable|image|max:2048', 
            'unit_id' => 'required|exists:units,id',
        ]);
    
        foreach ($request->name as $index => $name) {
            
            $featureData = [
                'title' => $name,
                'image_path' => $request->description[$index] ?? null,
                'unit_id' => $request->unit_id,
                'type' => isset($request->image[$index]) ? "image" : "text",
            ];
    
    
            // ✅ إذا كان هناك صورة مرفوعة، احفظها
            if (isset($request->image[$index])) {
                $featureData['image_path'] = $request->image[$index]->store('unit_features', 'public');
            }

            UnitFeature::create($featureData);
        }
    
        return redirect()->back()->with('success', 'All features added successfully!');
    }
    


    /**
     * Display the specified resource.
     */
    public function show(UnitFeature $unitFeature)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(UnitFeature $unitFeature)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, UnitFeature $unitFeature)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(UnitFeature $unitfeature)
    {
        if ($unitfeature->image_path) {
            $imagePath = public_path('storage/' . $unitfeature->image_path);
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
        }
        $unitfeature->delete();
        session()->flash('success', "unit feature '{$unitfeature->title}' has been deleted.");
        return redirect()->back();
        //
    }
}
