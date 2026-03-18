<?php

namespace App\Http\Controllers;

use App\Models\Contract;
use App\Models\ContractService;
use App\Models\Invoice;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ContractServiceController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'contract_id' => 'required|exists:contracts,id',
            'service_id' => 'required|exists:services,id', // ✅ تعديل الاسم الصحيح
            'custom_price' => 'required|numeric|min:0',
        ]);

        // ✅ التحقق من عدم تكرار الخدمة قبل الإضافة
        $existingService = ContractService::where('contract_id', $request->contract_id)
            ->where('service_id', $request->service_id)
            ->exists();

        if ($existingService) {

            session()->flash('errors', "This service is already added to the contract.");

            return redirect()->back();
        }

        $contractService = ContractService::create([
            'contract_id' => $request->contract_id,
            'service_id' => $request->service_id,
            'custom_price' => $request->custom_price
        ]);
        // ✅ جلب العقد المرتبط بالخدمة
        $contract = $contractService->contract;

        // ✅ حساب مجموع custom_price لكل الخدمات الخاصة بالعقد
        $totalServicesCost = $contract->services()->sum('contract_services.custom_price');

        // ✅ تحديث جميع الفواتير بعد تاريخ التعديل
        Invoice::where('contract_id', $contract->id)
            ->where('invoice_date', '>', now())->where('status', '!=', 'paid') // ✅ تعديل الفواتير المستقبلية فقط
            ->update(['services_cost' => $totalServicesCost]);

        return redirect()->route('contracts.show', ['contract' => $contract->id])
            ->with('success', 'Service added successfully.');
    }
    public function addDate(Request $request)
    {
        $request->validate([
            'contract_id' => 'required|exists:contracts,id',
            'end_date' => 'required|date|after:today',
        ]);

        $contract = Contract::findOrFail($request->contract_id);

        // التاريخ القديم
        $oldEndDate = $contract->end_date
            ? Carbon::parse($contract->end_date)->startOfMonth()
            : Carbon::parse($contract->start_date)->startOfMonth();

        // التاريخ الجديد
        $newEndDate = Carbon::parse($request->end_date)->startOfMonth();

        // تحديث تاريخ نهاية العقد
        $contract->update(['end_date' => $newEndDate]);

        // نبدأ من الشهر اللي بعد القديم
        $currentDate = $oldEndDate->copy()->addMonth();

        while ($currentDate->lte($newEndDate)) {
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

        return redirect()->route('contracts.show', ['contract' => $contract->id])
            ->with('success', 'Contract extended and invoices generated.');
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ContractService $contractService)
    {
        $request->validate([
            'custom_price' => 'required|numeric|min:0',
        ]);

        // ✅ تحديث سعر الخدمة في جدول contract_services
        $contractService->update([
            'custom_price' => $request->custom_price,
        ]);

        // ✅ جلب العقد المرتبط بالخدمة
        $contract = $contractService->contract;

        // ✅ حساب مجموع custom_price لكل الخدمات الخاصة بالعقد
        $totalServicesCost = $contract->services()->sum('contract_services.custom_price');

        // ✅ تحديث جميع الفواتير بعد تاريخ التعديل
        Invoice::where('contract_id', $contract->id)
            ->where('invoice_date', '>', now())->where('status', '!=', 'paid')->where('type', '=', 'service') // ✅ تعديل الفواتير المستقبلية فقط
            ->update(['services_cost' => $totalServicesCost]);

        return redirect()->route('contracts.show', ['contract' => $contract->id])
            ->with('success', 'Service price updated successfully, and invoices were adjusted.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ContractService $contractService)
    {
        $contractService->delete();
        // ✅ جلب العقد المرتبط بالخدمة
        $contract = $contractService->contract;

        // ✅ حساب مجموع custom_price لكل الخدمات الخاصة بالعقد
        $totalServicesCost = $contract->services()->sum('contract_services.custom_price');

        // ✅ تحديث جميع الفواتير بعد تاريخ التعديل
        Invoice::where('contract_id', $contract->id)
            ->where('invoice_date', '>', now())->where('status', '!=', 'paid') // ✅ تعديل الفواتير المستقبلية فقط
            ->update(['services_cost' => $totalServicesCost]);

        return redirect()->route('contracts.show', ['contract' => $contract->id]);
    }
}
