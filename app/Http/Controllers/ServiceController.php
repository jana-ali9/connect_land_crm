<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    
     public function index(Request $request)
     {
         $query = Service::query();
 
         if ($request->has('search')) {
             $search = $request->input('search');
             $query->where('name', 'LIKE', "%$search%")
                 ->orWhere('description', 'LIKE', "%$search%");
         }
 
         $allservices= $query->paginate(10)->appends(['search' => $request->search]);
 
         return view('services.index', compact('allservices'));
     }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('services.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    
     public function store()
     {
         request()->validate([
             "name" => ['required', 'min:3'],
             "description" => ['required'],
             "type" => ['required'],
             "default_price" => ['required','numeric'],
         ]);
         $data = request()->all();
         Service::create([
             "name" => $data['name'],
             "description" => $data['description'],
             "type" => $data['type'],
             "default_price" => $data['default_price'],
         ]);
         session()->flash('success', "Service addes success");
         return to_route("services.index");
     }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Service $service)
    {
        return view('services.edit', compact('service'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Service $service)
    {
        request()->validate([
            "name" => ['required', 'min:3'],
            "description" => ['required'],
            "type" => ['required'],
            "default_price" => ['required','numeric'],
        ]);

        $data = request()->all();
        $service->update([
            "name" => $data['name'],
            "description" => $data['description'],
            "type" => $data['type'],
            "default_price" => $data['default_price'],
        ]);

        session()->flash('success', "We updated service named $service->name");

        return to_route("services.index");
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Service $service)
    {
        $service->delete();
        session()->flash('success', "service {$service->name} has been deleted.");
    
        return to_route("services.index");
    }
}
