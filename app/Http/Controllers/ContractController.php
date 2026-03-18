<?php

namespace App\Http\Controllers;

use App\Models\Building;
use App\Models\Client;
use App\Models\Contract;
use App\Models\ContractImage;
use App\Models\ContractService;
use App\Models\Invoice;
use App\Models\Service;
use App\Models\Unit;
use App\Models\Land;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ContractController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Contract::query();

        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->whereHas('unit', function ($u) use ($search) {
                    $u->where('name', 'LIKE', "%{$search}%");
                })
                    ->orWhereHas('building', function ($b) use ($search) {
                        $b->where('name', 'LIKE', "%{$search}%");
                    })
                    ->orWhereHas('land', function ($l) use ($search) {
                        $l->where('name', 'LIKE', "%{$search}%");
                    })
                    ->orWhereHas('client', function ($c) use ($search) {
                        $c->where('name', 'LIKE', "%{$search}%");
                    });
            });
        }

        if ($request->filled('client_id')) {
            $query->where('client_id', $request->client_id);
        }

        if ($request->filled('status')) {
            $query->where('contract_status', $request->status);
        }

        if ($request->filled('type')) {
            $query->where('contract_type', $request->type);
        }

        if ($request->filled('min_price')) {
            $query->where('base_rent', '>=', $request->min_price);
        }

        if ($request->filled('max_price')) {
            $query->where('base_rent', '<=', $request->max_price);
        }

        $query->orderByRaw("
        CASE contract_status
            WHEN 'active' THEN 1
            WHEN 'suspended' THEN 2
            WHEN 'expired' THEN 3
            ELSE 4
        END
    ");

        $allcontracts = $query->paginate(10)->appends($request->all());
        $clients = Client::all();

        return view('contracts.index', compact('allcontracts', 'clients'));
    }




    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $services = Service::where('type', 'service')->get();
        $features = Service::where('type', 'feature')->get();
        $buildings = Building::where('is_payed', false)->get();
        $lands = Land::where('is_rented', 0)->where('is_payed', 0)->get();
        $building_id = $request->building_id ?? null;
        $unit_id = $request->unit_id ?? null;


        return view('contracts.create', compact(
            'services',
            'features',
            'buildings',
            'building_id',
            'unit_id',
            'lands'
        ));
    }

    public function createbuildings(Request $request)
    {
        $services = Service::where('type', 'service')->get();
        $features = Service::where('type', 'feature')->get();
        $buildings = Building::where('is_payed', false)->get();
        $lands = Land::where('is_rented', 0)->where('is_payed', 0)->get();
        $building_id = $request->building_id ?? null;
        $unit_id = $request->unit_id ?? null;
        return view('contracts.createbuilding', compact(
            'services',
            'features',
            'buildings',
            'building_id',
            'unit_id',
            'lands'
        ));
    }

    /**
     * Store a newly created resource in storage.
     */

    public function store(Request $request)
    {
        // determine property type from the toggle
        $propertyType = $request->property_type === 'land' ? 'land' : 'building';
        // validate depending on property type
        if ($propertyType === 'land') {
            $request->validate([
                'land_id' => 'required|exists:lands,id',
                'client_id' => 'required|exists:clients,id',
                'start_date' => 'required|date',
                'end_date' => 'nullable|date|after:start_date',
                'base_rent' => 'required|numeric|min:0',
                'increase_rate' => 'required|numeric|min:0',
                'increase_frequency' => 'required|integer|min:0',
                'billing_frequency' => 'required|integer|min:0',
                'amount_paid' => 'required|numeric|min:0',
                'contract_video' => 'nullable|file|mimes:mp4,avi,mov,wmv|max:10240',
                'services' => 'nullable|array'
            ]);
        } else {
            $request->validate([
                'unit_id' => 'required|exists:units,id',
                'building_id' => 'required|exists:buildings,id',
                'client_id' => 'required|exists:clients,id',
                'start_date' => 'required|date',
                'end_date' => 'nullable|date|after:start_date',
                'base_rent' => 'required|numeric|min:0',
                'increase_rate' => 'required|numeric|min:0',
                'increase_frequency' => 'required|integer|min:0',
                'billing_frequency' => 'required|integer|min:0',
                'amount_paid' => 'required|numeric|min:0',
                'contract_video' => 'nullable|file|mimes:mp4,avi,mov,wmv|max:10240',
                'services' => 'nullable|array'
            ]);
        }

        // create contract
        $contract = Contract::create([
            'unit_id' => $propertyType === 'building' ? $request->unit_id : null,
            'building_id' => $propertyType === 'building' ? $request->building_id : null,
            'land_id' => $propertyType === 'land' ? $request->land_id : null,
            'client_id' => $request->client_id,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'base_rent' => $request->base_rent,
            'increase_rate' => $request->increase_rate,
            'increase_frequency' => $request->increase_frequency,
            'billing_frequency' => $request->billing_frequency,
            'insurance' => $request->insurance ?? 0,
            'contract_status' => 'active',
            'contract_type' => 'rent', // always rent
            'property_type' => $propertyType,
            'amount_for_services' => 0,

        ]);

        $contract->update([
            'invoice_price' => $request->amount_paid,
        ]);

        // mark property as rented
        if ($propertyType === 'land') {
            Land::where('id', $request->land_id)->update(['is_rented' => 1, 'is_payed' => 0]);
        } else {
            Unit::where('id', $request->unit_id)->update(['is_rented' => 1]);
        }

        // handle services
        if ($request->has('services')) {
            $formattedServices = [];
            foreach ($request->services as $service) {
                if (is_array($service) && isset($service['id'], $service['type'])) {
                    if ($service['type'] === 'service' && isset($service['price'])) {
                        $formattedServices[$service['id']] = [
                            'custom_price' => (float) $service['price'],
                        ];
                    } elseif ($service['type'] === 'feature') {
                        $formattedServices[$service['id']] = [
                            'custom_price' => null,
                        ];
                    }
                }
            }
            if (!empty($formattedServices)) {
                $contract->services()->attach($formattedServices);
            }
        }

        // video
        if ($request->hasFile('contract_video')) {
            $video = $request->file('contract_video');
            if ($video->isValid()) {
                $path = $video->store('contract_videos', 'public');
                $contract->update(['contract_video' => $path]);
            }
        }

        // images
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('contract_images', 'public');
                ContractImage::create([
                    'contract_id' => $contract->id,
                    'image_path' => $path
                ]);
            }
        }

        // invoices
        $startDate = Carbon::parse($request->start_date);
        $endDate = $request->end_date ? Carbon::parse($request->end_date) : null;
        $duration = $endDate ? $startDate->diffInMonths($endDate) : 0;

        $nextDate = $startDate->copy();
        $freq = (int) $request->billing_frequency;
        $rate = $request->increase_rate / 100;
        $currentRent = $contract->base_rent;
        $allPaid = $request->amount_paid;

        for ($i = 0; $i < $duration; $i += $freq) {
            $amountDue = $currentRent;
            $amountPaid = min($allPaid, $amountDue);

            $invoice = Invoice::create([
                'contract_id' => $contract->id,
                'client_id' => $contract->client_id,
                'invoice_date' => $nextDate->toDateString(),
                'amount_due' => $amountDue,
                'amount_paid' => $amountPaid,
                'type' => $propertyType,
                'services_cost' => 0,
                'status' => ($amountPaid == $amountDue) ? 'paid' : 'pending',
            ]);

            \App\Models\PaymentHistory::create([
                'invoice_id' => $invoice->id,
                'contract_id' => $contract->id,
                'client_id' => $contract->client_id,
                'amount_paid' => $amountPaid,
                'due_after_payment' => max($amountDue - $amountPaid, 0),
                'payment_date' => now(),
            ]);

            $allPaid = max(0, $allPaid - $amountPaid);
            $nextDate->addMonths($freq);

            if (floor($i / 12) < floor(($i + $freq) / 12)) {
                $currentRent += $currentRent * $rate;
            }
        }

        $this->createMonthlyInvoices($contract);

        session()->flash('success', 'Contract added successfully');
        return to_route("contracts.index");
    }





    public function storebuilding(Request $request)
    {
        // determine type from checkbox (land toggle)
        $propertyType = $request->property_type === 'land' ? 'land' : 'building';
        // validation rules
        $rules = [
            'client_id' => 'required|exists:clients,id',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date',
            'base_rent' => 'required|numeric|min:0',
            'amount_paid' => 'required|numeric|min:0',
            'invoice_price' => 'nullable|numeric|min:0',
            'billing_frequency' => 'nullable|integer|min:0',
            'contract_file' => 'nullable|file|mimes:pdf,doc,docx|max:2048',
            'services' => 'nullable|array',
            'contract_video' => 'nullable|file|mimes:mp4,avi,mov,wmv|max:10240'
        ];

        if ($propertyType === 'building') {
            $rules['building_id'] = 'required|exists:buildings,id';
            $rules['unit_id'] = 'nullable|exists:units,id';
        } else {
            $rules['land_id'] = 'required|exists:lands,id';
        }

        $validated = $request->validate($rules);

        $contract = Contract::create(array_merge(
            $request->except('services', 'images'),
            [
                'contract_type' => 'sale',
                'property_type' => $propertyType
            ]
        ));

        $contract->update([
            'increase_rate' => $request->amount_paid,
        ]);

        // set is_payed for the purchased property
        if ($propertyType === 'building') {
            if ($request->unit_id == null) {
                $building = Building::find($request->building_id);
                $building->update(['is_payed' => true, "end_price" => $request->base_rent]);
                foreach ($building->units as $unit) {
                    $unit->update([
                        "is_payed" => true,
                    ]);
                }
            } else {
                Unit::where('id', $request->unit_id)->update([
                    "end_price" => $request->base_rent,
                    "is_payed" => true,
                ]);
            }
        } else {
            // propertyType is land
            Land::where('id', $request->land_id)->update([
                "is_payed" => true
                // no end_price because lands table does not have that column
            ]);
        }

        // first invoice
        $startDate = Carbon::parse($request->start_date);
        $invoice = Invoice::create([
            'contract_id' => $contract->id,
            'client_id' => $contract->client_id,
            'invoice_date' => $startDate,
            'amount_due' => $request->amount_paid,
            'amount_paid' => $request->amount_paid,
            'services_cost' => 0,
            'status' => 'paid',
            'type' => $propertyType,
        ]);
        \App\Models\PaymentHistory::create([
            'invoice_id' => $invoice->id,
            'contract_id' => $contract->id,
            'client_id' => $contract->client_id,
            'amount_paid' => $request->amount_paid,
            'due_after_payment' => max($request->invoice_price - $request->amount_paid, 0),
            'payment_date' => now(),
        ]);

        // services
        $formattedServices = [];
        if (!empty($request->services) && is_array($request->services)) {
            foreach ($request->services as $service) {
                if (is_array($service) && isset($service['id'], $service['type'])) {
                    $type = $service['type'];
                    if ($type === 'service' && isset($service['price'])) {
                        $formattedServices[$service['id']] = [
                            'custom_price' => (float) $service['price'],
                        ];
                    } elseif ($type === 'feature') {
                        $formattedServices[$service['id']] = [
                            'custom_price' => null,
                        ];
                    }
                }
            }
            if (!empty($formattedServices)) {
                $contract->services()->attach($formattedServices);
            }
        }

        // video
        if ($request->hasFile('contract_video')) {
            $video = $request->file('contract_video');
            if ($video->isValid()) {
                $videoPath = $video->store('contract_videos', 'public');
                $contract->update(['contract_video' => $videoPath]);
            }
        }

        // images
        if ($request->hasFile('images') && is_array($request->file('images'))) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('contract_images', 'public');
                ContractImage::create([
                    'contract_id' => $contract->id,
                    'image_path' => $path
                ]);
            }
        }

        // schedule future invoices
        $billingFrequency = (int) $request->billing_frequency;
        $invoicePrice = (float) $request->invoice_price;
        $baseRent = (float) $request->base_rent;
        $amountPaid = (float) $request->amount_paid;
        $remaining = $baseRent - $amountPaid;

        if ($billingFrequency > 0 && $remaining > 0 && $invoicePrice > 0) {
            $invoicesCount = floor($remaining / $invoicePrice);
            $remainingAfterInvoices = $remaining - ($invoicesCount * $invoicePrice);

            $invoiceDate = $startDate->copy()->addMonths($billingFrequency);

            for ($i = 0; $i < $invoicesCount; $i++) {
                $invoice = Invoice::create([
                    'contract_id' => $contract->id,
                    'client_id' => $contract->client_id,
                    'invoice_date' => $invoiceDate->toDateString(),
                    'amount_due' => $invoicePrice,
                    'amount_paid' => 0,
                    'services_cost' => 0,
                    'status' => 'pending',
                    'type' => $propertyType,
                ]);


                $invoiceDate->addMonths($billingFrequency);
            }

            if ($remainingAfterInvoices > 0) {
                Invoice::create([
                    'contract_id' => $contract->id,
                    'client_id' => $contract->client_id,
                    'invoice_date' => $invoiceDate->toDateString(),
                    'amount_due' => $remainingAfterInvoices,
                    'amount_paid' => 0,
                    'services_cost' => 0,
                    'status' => 'pending',
                    'type' => $propertyType,
                ]);
            }
        }

        // recurring monthly invoices if needed
        $this->createMonthlyInvoices($contract);

        session()->flash('success', trans("Contract added successfully"));
        return to_route("contracts.index");
    }


    private function createMonthlyInvoices(Contract $contract)
    {
        $startDate = Carbon::parse($contract->start_date)->startOfMonth();
        $endDate = $contract->end_date ? Carbon::parse($contract->end_date)->startOfMonth() : Carbon::now()->addYears(10)->startOfMonth();

        // نبدأ من تاريخ البداية
        $currentDate = $startDate->copy();

        while ($currentDate->lte($endDate)) {
            if ($contract->services()->sum('custom_price') > 0) {
                Invoice::create([
                    'contract_id' => $contract->id,
                    'client_id' => $contract->client_id,
                    'invoice_date' => $currentDate->toDateString(),
                    'amount_due' => 0,
                    'amount_paid' => 0,
                    'services_cost' => $contract->services()->sum('custom_price'),
                    'type' => 'service',
                    'status' => 'pending',
                ]);
            }

            $currentDate->addMonth();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Contract $contract)
    {
        $contract->load(['images']);

        $services = $contract->services()
            ->where('type', 'service')
            ->withPivot('custom_price')
            ->get()
            ->map(function ($service) {
                $service->price = $service->pivot->custom_price ?? $service->default_price;
                return $service;
            });

        $invoices = $contract->invoices()
            ->where('type', 'unit')
            ->orderByRaw("CASE WHEN status = 'paid' THEN 1 ELSE 0 END, invoice_date ASC")
            ->get();

        $invoicesServices = $contract->invoices()
            ->where('type', 'service')
            ->where(function ($query) {
                $today = Carbon::now();
                $query->where('invoice_date', '<', $today->toDateString())
                    ->orWhereRaw(
                       "DATE_FORMAT(invoice_date, '%m') = ? AND DATE_FORMAT(invoice_date, '%Y') = ?",
                    [str_pad($today->month, 2, '0', STR_PAD_LEFT), $today->year]
                );
        })
            ->orderBy('invoice_date', 'ASC')
            ->get();

        $allservices = Service::all();
        $features = $contract->services()->where('type', 'feature')->get();

        if ($contract->contract_type == "sale") {
            return view('contracts.showbuilding', compact(
                'contract',
                'services',
                'features',
                'invoices',
                'invoicesServices',
                'allservices'
            ));
        }

        return view('contracts.show', compact(
            'contract',
            'services',
            'features',
            'invoices',
            'invoicesServices',
            'allservices'
        ));
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Contract $contract)
    {
        $services = Service::where('type', 'service')->get();
        $features = Service::where('type', 'feature')->get();
        $buildings = Building::all();

        // Filter lands: only show lands that are not rented or paid, or the current land in the contract
        $lands = $contract->property_type === 'land'
            ? Land::where(function ($query) use ($contract) {
                $query->where('is_rented', '!=', 1)
                    ->where('is_payed', '!=', 1)
                    ->orWhere('id', $contract->land_id); // Allow the current land used
            })->get()
            : [];

        $propertyType = $contract->property_type;
        $building_id = $contract->building_id ?? null;
        $unit_id = $contract->unit_id ?? null;
        $land_id = $contract->land_id ?? null;

        if ($contract->contract_type == "rent") {
            return view('contracts.edit', compact(
                'contract',
                'services',
                'features',
                'buildings',
                'building_id',
                'unit_id',
                'lands',
                'propertyType',
                'land_id'
            ));
        } else {
            return view('contracts.editbuilding', compact(
                'contract',
                'services',
                'features',
                'buildings',
                'building_id',
                'unit_id',
                'lands',
                'propertyType',
                'land_id'
            ));
        }
    }

    public function update(Request $request, Contract $contract)
    {

        $isLand = $contract->property_type === 'land';

        $rules = [
            'client_id' => 'required|exists:clients,id',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date',
            'base_rent' => 'required|numeric|min:0',
            'increase_rate' => 'required|numeric|min:0',
            'increase_frequency' => 'required|integer|min:0',
            'billing_frequency' => 'required|integer|min:0',
            'amount_paid' => 'required|numeric|min:0',
            'insurance' => 'nullable|numeric|min:0',
            'contract_file' => 'nullable|file|mimes:pdf,doc,docx|max:2048',
            'contract_video' => 'nullable|file|mimes:mp4,avi,mov,wmv|max:10240',
            'services' => 'nullable|array',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ];

        if ($isLand) {
            $rules['land_id'] = 'required|exists:lands,id';
        } else {
            $rules['unit_id'] = 'required|exists:units,id';
        }

        $hasPaidInvoices = Invoice::where('contract_id', $contract->id)
            ->where('type', $isLand ? 'land' : 'unit')
            ->where(function ($q) {
                $q->where('status', 'paid')
                    ->orWhere('amount_paid', '>', 0);
            })
            ->exists();

        if ($hasPaidInvoices) {
            // Tell the user and bounce back without changing anything
            return back()
                ->withInput()
                ->withErrors(['contract' => __("You can’t update this contract because it already has paid invoices.")]);
        }

        $request->validate($rules);

        // Save last paid before update
        $lastAmountPaid = $contract->amount_paid;

        // Update contract info
        $contract->update([
            'land_id' => $isLand ? $request->land_id : null,
            'unit_id' => $isLand ? null : $request->unit_id,
            'client_id' => $request->client_id,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'base_rent' => $request->base_rent,
            'increase_rate' => $request->increase_rate,
            'increase_frequency' => $request->increase_frequency,
            'billing_frequency' => $request->billing_frequency,
            'invoice_price' => $request->amount_paid,
            'insurance' => $request->insurance,
        ]);

        // Update property status
        if (!$isLand) {
            Unit::where('id', $request->unit_id)->update(['is_rented' => true]);
        }

        // Handle video
        if ($request->hasFile('contract_video')) {
            $videoPath = $request->file('contract_video')->store('contract_videos', 'public');
            $contract->update(['contract_video' => $videoPath]);
        }

        // Handle images
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('contract_images', 'public');
                ContractImage::create([
                    'contract_id' => $contract->id,
                    'image_path' => $path,
                ]);
            }
        }

        // Update services
        $contract->services()->detach();
        $formattedServices = [];

        if (!empty($request->services) && is_array($request->services)) {
            foreach ($request->services as $service) {
                if (is_array($service) && isset($service['id'], $service['type'])) {
                    $formattedServices[$service['id']] = [
                        'custom_price' => $service['type'] === 'service' ? (float) ($service['price'] ?? 0) : null,
                    ];
                }
            }
            $contract->services()->attach($formattedServices);
        }

        // Delete and regenerate unit/land invoices
        Invoice::where('contract_id', $contract->id)
            ->where('type', $isLand ? 'land' : 'unit')
            ->delete();

        $totalPaidPreviously = Invoice::where('contract_id', $contract->id)
            ->where('type', $isLand ? 'land' : 'unit')
            ->sum('amount_paid') + ($request->amount_paid - $lastAmountPaid);

        $startDate = Carbon::parse($contract->start_date);
        $endDate = $contract->end_date ? Carbon::parse($contract->end_date) : null;
        $contractDuration = $endDate ? $startDate->diffInMonths($endDate) : 0;

        $billingFrequency = (int) $contract->billing_frequency;
        $increaseRate = $contract->increase_rate / 100;
        $currentRent = $contract->base_rent;
        $nextInvoiceDate = $startDate->copy();

        for ($i = 0; $i < $contractDuration; $i += $billingFrequency) {
            $amountDue = $currentRent;
            $amountPaid = min($totalPaidPreviously, $amountDue);

            Invoice::create([
                'contract_id' => $contract->id,
                'client_id' => $contract->client_id,
                'invoice_date' => $nextInvoiceDate->toDateString(),
                'amount_due' => $amountDue,
                'amount_paid' => $amountPaid,
                'services_cost' => 0,
                'status' => ($amountPaid == $amountDue) ? 'paid' : 'pending',
                'type' => $isLand ? 'land' : 'unit',
            ]);

            $totalPaidPreviously -= $amountPaid;
            $nextInvoiceDate->addMonths($billingFrequency);

            if (floor($i / 12) < floor(($i + $billingFrequency) / 12)) {
                $currentRent += $currentRent * $increaseRate;
            }
        }

        session()->flash('success', trans("Contract updated successfully"));
        return redirect()->route("contracts.index");
    }


    public function updatebuilding(Request $request, Contract $contract)
    {
        $request->validate([
            'building_id' => 'required|exists:buildings,id',
            'unit_id' => 'nullable|exists:units,id',
            'client_id' => 'required|exists:clients,id',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date',
            'base_rent' => 'required|numeric|min:0',
            'amount_paid' => 'required|numeric|min:0',
            'invoice_price' => 'nullable|numeric|min:0',
            'billing_frequency' => 'nullable|integer|min:0',
            'contract_file' => 'nullable|file|mimes:pdf,doc,docx|max:2048',
            'contract_video' => 'nullable|file|mimes:mp4,avi,mov,wmv|max:10240'
        ]);

        // 🚫 Block updates if any invoice on this contract has been paid (fully or partially)
        $hasPaidInvoices = $contract->invoices()
            ->where(function ($q) {
                $q->where('status', 'paid')
                    ->orWhere('amount_paid', '>', 0);
            })
            ->exists();

        if ($hasPaidInvoices) {
            return back()
                ->withInput()
                ->withErrors(['contract' => __("You can’t update this contract because it already has paid invoices.")]);
        }


        // ✅ تحديث بيانات العقد
        $contract->update(array_merge(
            $request->except('images'),
            ['contract_type' => 'sale', 'increase_rate' => $request->amount_paid]
        ));

        // ✅ تحديث حالة الوحدة أو المبنى
        if ($request->unit_id == null) {
            $building = Building::find($request->building_id);
            $building->update(['is_payed' => true, "end_price" => $request->base_rent]);
            foreach ($building->units as $unit) {
                $unit->update(["is_payed" => true]);
            }
        } else {
            Unit::where('id', $request->unit_id)->update([
                "end_price" => $request->base_rent,
                "is_payed" => true,
            ]);
        }

        // ✅ رفع الفيديو إن وجد
        if ($request->hasFile('contract_video')) {
            $video = $request->file('contract_video');
            if ($video->isValid()) {
                $videoPath = $video->store('contract_videos', 'public');
                $contract->update(['contract_video' => $videoPath]);
            }
        }

        // ✅ رفع الصور إن وجدت
        if ($request->hasFile('images') && is_array($request->file('images'))) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('contract_images', 'public');
                ContractImage::create([
                    'contract_id' => $contract->id,
                    'image_path' => $path
                ]);
            }
        }

        // ✅ حذف الفواتير غير المدفوعة
        $contract->invoices()->where('type', 'unit')->where('status', '!=', 'paid')->delete();

        // ✅ حساب الفواتير
        $startDate = Carbon::parse($request->start_date);
        $billingFrequency = (int) $request->billing_frequency;
        $invoicePrice = (float) $request->invoice_price;
        $baseRent = (float) $request->base_rent;
        $amountPaidNow = (float) $request->amount_paid;

        // ✅ تعديل أول فاتورة مدفوعة أو إنشائها
        $firstPaidInvoice = $contract->invoices()
            ->where('type', 'unit')
            ->where('status', 'paid')
            ->orderBy('invoice_date', 'asc')
            ->first();

        if ($firstPaidInvoice) {
            $firstPaidInvoice->update([
                'amount_due' => $amountPaidNow,
                'amount_paid' => $amountPaidNow,
            ]);
        } else {
            Invoice::create([
                'contract_id' => $contract->id,
                'client_id' => $contract->client_id,
                'invoice_date' => $startDate->toDateString(),
                'amount_due' => $amountPaidNow,
                'amount_paid' => $amountPaidNow,
                'services_cost' => 0,
                'status' => 'paid',
                'type' => 'unit',
            ]);
        }

        // ✅ احسب المتبقي
        $totalPaid = $contract->invoices()
            ->where('type', 'unit')
            ->where('status', 'paid')
            ->sum('amount_paid');

        $remaining = $baseRent - $totalPaid;

        if ($remaining > 0 && $invoicePrice > 0 && $billingFrequency > 0) {
            $invoiceDate = $startDate->copy();
            $paidCount = $contract->invoices()
                ->where('type', 'unit')
                ->where('status', 'paid')
                ->count();

            $invoiceDate->addMonths($billingFrequency * $paidCount);

            $invoiceCount = floor($remaining / $invoicePrice);
            $lastInvoiceAmount = $remaining - ($invoiceCount * $invoicePrice);

            for ($i = 0; $i < $invoiceCount; $i++) {
                Invoice::create([
                    'contract_id' => $contract->id,
                    'client_id' => $contract->client_id,
                    'invoice_date' => $invoiceDate->toDateString(),
                    'amount_due' => $invoicePrice,
                    'amount_paid' => 0,
                    'services_cost' => 0,
                    'status' => 'pending',
                    'type' => 'unit',
                ]);
                $invoiceDate->addMonths($billingFrequency);
            }

            if ($lastInvoiceAmount > 0) {
                Invoice::create([
                    'contract_id' => $contract->id,
                    'client_id' => $contract->client_id,
                    'invoice_date' => $invoiceDate->toDateString(),
                    'amount_due' => $lastInvoiceAmount,
                    'amount_paid' => 0,
                    'services_cost' => 0,
                    'status' => 'pending',
                    'type' => 'unit',
                ]);
            }
        }

        session()->flash('success', trans("Contract updated successfully"));
        return to_route("contracts.index");
    }


    /**
     * Update the specified resource in storage.
     */
    /**
     * Cancel the specified contract.
     */
    public function cancellation(Contract $contract)
    {
       try {
        // 1. تحديث حالة الفواتير المستقبلية قبل أي شيء
        $today = \Carbon\Carbon::now()->toDateString();
        $contract->invoices()
            ->where('invoice_date', '>', $today)
            ->update(['status' => 'suspended']);

        // 2. تحديث حالة العقد (اختياري لأننا سنحذفه، لكن للأمان)
        $contract->update(['contract_status' => 'suspended']);

        // 3. استدعاء دالة الحذف (تأكد من تنفيذ خطوة الـ Nullable في قاعدة البيانات أولاً)
        return $this->destroy($contract);

    } catch (\Exception $e) {
        return back()->with('error', 'فشلت العملية: ' . $e->getMessage());
    }
}
    public function deleteVideo($id)
    {
        $contract = Contract::findOrFail($id);

        if ($contract->contract_video && Storage::disk('public')->exists($contract->contract_video)) {
            Storage::disk('public')->delete($contract->contract_video);
        }

        $contract->update(['contract_video' => null]);

        return back()->with('success', 'Contract video deleted successfully.');
    }



    public function deleteImage($id)
    {
        $image = ContractImage::findOrFail($id);

        if (Storage::disk('public')->exists($image->image_path)) {
            Storage::disk('public')->delete($image->image_path);
        }

        $image->delete();

        return back()->with('success', 'Contract image deleted successfully.');
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Contract $contract)
    {
   try {
        $contract->invoices()->update(['contract_id' => null]);
        // تحديث حالة العقار (أرض، شقة، أو مبنى كامل) بشكل آمن
        if ($contract->property_type === 'land' && $contract->land) {
            $contract->land->update(['is_rented' => 0, 'is_payed' => 0]);
        } else {
            if ($contract->contract_type == "rent") {
                // استخدام optional لمنع الخطأ إذا كانت الوحدة null
                optional($contract->unit)->update(["is_rented" => false]);
            } else {
                if ($contract->unit_id == null && $contract->building) {
                    $contract->building->update(['is_payed' => false, "end_price" => null]);
                    foreach ($contract->building->units as $unit) {
                        $unit->update(["is_payed" => false]);
                    }
                } else {
                    optional($contract->unit)->update(['is_payed' => false, "end_price" => null]);
                }
            }
        }
        // تحميل الصور والفيديو
        $contract->load(['images']);

        // ✅ حذف الصور من التخزين
        foreach ($contract->images as $image) {
            $imagePath = public_path('storage/' . $image->image_path); // تأكد من استدعاء `image_path`
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
        }

        // ✅ حذف الفيديو من التخزين
        if ($contract->contract_video) {
            $videoPath = public_path('storage/' . $contract->contract_video);
            if (file_exists($videoPath)) {
                unlink($videoPath);
            }
        }

        // ✅ حذف العقد من قاعدة البيانات
        $contract->delete();

        session()->flash('success', "Contract '{$contract->id}' has been deleted.");

        return to_route("contracts.index");
    }
    catch (\Exception $e) {
        // في حال حدوث أي خطأ، يتم العودة للخلف مع رسالة الخطأ
        return back()->with('error', 'Error deleting contract: ' . $e->getMessage());
    }
}
}