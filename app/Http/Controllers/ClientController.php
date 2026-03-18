<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    //
    public function index(Request $request)
    {
        $query = Client::query();

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where('name', 'LIKE', "%$search%")
                ->orWhere('phone', 'LIKE', "%$search%");
        }

        $allclients = $query->paginate(10)->appends(['search' => $request->search]);

        return view('clients.index', compact('allclients'));
    }

    public function search(Request $request)
    {
        $search = $request->q;
        $clients = Client::where('name', 'LIKE', '%' . $search . '%')
            ->orWhere('phone', 'LIKE', "%$search%")->get();
        return response()->json($clients);
    }
    public function create()
    {
        return view('clients.create');
    }
    public function store()
    {
        request()->validate([
            "name" => ['required', 'min:3'],
            "phone" => ['required', 'min:8'],
        ]);
        $data = request()->all();
        Client::create([
            "name" => $data['name'],
            "phone" => $data['phone'],
        ]);
        session()->flash('success', "Client addes success");
        return to_route("clients.index");
    }
    public function store1(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20|min:8'
        ]);

        $client = Client::create([
            'name' => $request->name,
            'phone' => $request->phone
        ]);

        return response()->json([
            'success' => true,
            'client_id' => $client->id
        ]);
    }
    public function edit(Client $client)
    {
        return view('clients.edit', compact('client'));
    }
    public function update(Client $client)
    {

        request()->validate([
            "name" => ['required', 'min:3'],
            "phone" => ['required', 'min:8'],
        ]);

        $data = request()->all();
        $client->update([
            "name" => $data['name'],
            "phone" => $data['phone'],
        ]);

        session()->flash('success', "We updated client named $client->name");

        return to_route("clients.index");
    }
    public function destroy(Client $client)
    {
        $client->delete();
        session()->flash('success', "client '{$client->name}' has been deleted.");

        return to_route("clients.index");
    }
}
