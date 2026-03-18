<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BuildingController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\ContractController;
use App\Http\Controllers\ContractServiceController;
use App\Http\Controllers\ExpenseCategoryController;
use App\Http\Controllers\ExpenseOfferController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\RolesController;
use App\Http\Controllers\RoutingController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\UnitExpenseController;
use App\Http\Controllers\UnitFeatureController;
use App\Http\Controllers\LandController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;




Route::get('/sanctum/csrf-cookie', function () {
    return response()->noContent();
});

Route::post('/login', function (Request $request) {
    $credentials = $request->validate([
        'email' => ['required', 'email'],
        'password' => ['required', 'string'],
    ]);

    if (!Auth::attempt($credentials, true)) {
        throw ValidationException::withMessages(['email' => 'Invalid credentials.']);
    }

    $request->session()->regenerate();
    return response()->json(['ok' => true]);
});

require __DIR__ . '/auth.php';
Route::post("login", [AuthController::class, 'login'])->name("login");
Route::get('gologin', [AuthController::class, "gologin"])->name('gologin');
Route::get('/invoice/{invoice}/view', [InvoiceController::class, 'viewInvoice'])->name('invoice.view');


Route::get('units/by-building/{building}', function ($buildingId) {
    return \App\Models\Unit::where('building_id', $buildingId)
        ->select('id', 'name')
        ->get();
})->name('units.byBuilding')->middleware("permission:read units");


Route::middleware('adminCheck')->group(
    function () {
        //Admin

        $Name = "users";
        Route::get('admins/', [AuthController::class, 'index'])->name("admins.index")->middleware("permission:read $Name");
        Route::get('admins/create', [AuthController::class, 'create'])->name("admins.create")->middleware("permission:create $Name");
        Route::post('admins/', [AuthController::class, 'store'])->name("admins.store")->middleware("permission:create $Name");
        Route::get('/admins/{user}/edit', [AuthController::class, 'edit'])->name("admins.edit")->middleware("permission:update $Name");
        Route::put('/admins/{user}/', [AuthController::class, 'update'])->name("admins.update")->middleware("permission:update $Name");
        Route::delete('/admins/{user}/', [AuthController::class, 'destroy'])->name("admins.destroy")->middleware("permission:delete $Name");


        //Roles
        $Name = 'roles';
        Route::get("$Name/", [RolesController::class, 'index'])->name("$Name.index")->middleware("permission:read $Name");
        Route::get("$Name/create", [RolesController::class, 'create'])->name("$Name.create")->middleware("permission:create $Name");
        Route::post("$Name/", [RolesController::class, 'store'])->name("$Name.store")->middleware("permission:create $Name");
        Route::get("/$Name/{role}/edit", [RolesController::class, 'edit'])->name("$Name.edit")->middleware("permission:update $Name");
        Route::put("/$Name/{role}/", [RolesController::class, 'update'])->name("$Name.update")->middleware("permission:update $Name");
        Route::delete("/$Name/{role}/", [RolesController::class, 'destroy'])->name("$Name.destroy")->middleware("permission:delete $Name");


        //buildings
        $Name = 'buildings';
        Route::get("$Name/", [BuildingController::class, 'index'])->name("$Name.index")->middleware("permission:read $Name");
        Route::get("$Name/create", [BuildingController::class, 'create'])->name("$Name.create")->middleware("permission:create $Name");
        Route::get("$Name/show", [BuildingController::class, 'index'])->name("$Name.show")->middleware("permission:read $Name");
        Route::post("$Name/", [BuildingController::class, 'store'])->name("$Name.store")->middleware("permission:create $Name");
        Route::get("/$Name/{building}/edit", [BuildingController::class, 'edit'])->name("$Name.edit")->middleware("permission:update $Name");
        Route::put("/$Name/{building}/", [BuildingController::class, 'update'])->name("$Name.update")->middleware("permission:update $Name");
        Route::delete("/$Name/{building}/", [BuildingController::class, 'destroy'])->name("$Name.destroy")->middleware("permission:delete $Name");
        Route::get("/$Name/{building}", [BuildingController::class, 'show'])->name("$Name.show");
        Route::put("/$Name/{building}/sale", [BuildingController::class, 'sale'])->name("$Name.sale")->middleware("permission:update $Name");
        Route::get('/buildings/{building}/units', function (\App\Models\Building $building) {
            return $building->units()->select('id', 'name')->get();
        });

        // lands
        $Name = 'lands';
        Route::get("$Name/", [LandController::class, 'index'])->name("$Name.index")->middleware("permission:read $Name");
        Route::get("$Name/create", [LandController::class, 'create'])->name("$Name.create")->middleware("permission:create $Name");
        Route::post("$Name/", [LandController::class, 'store'])->name("$Name.store")->middleware("permission:create $Name");
        Route::get("/$Name/{land}/edit", [LandController::class, 'edit'])->name("$Name.edit")->middleware("permission:update $Name");
        Route::put("/$Name/{land}/", [LandController::class, 'update'])->name("$Name.update")->middleware("permission:update $Name");
        Route::delete("/$Name/{land}/", [LandController::class, 'destroy'])->name("$Name.destroy")->middleware("permission:delete $Name");
        Route::get("$Name/{land}", [LandController::class, 'show'])
            ->name("$Name.show")
            ->middleware("permission:read $Name");


        //clients
        $Name = 'clients';
        Route::get("$Name/", [ClientController::class, 'index'])->name("$Name.index")->middleware("permission:read $Name");
        Route::get("$Name/create", [ClientController::class, 'create'])->name("$Name.create")->middleware("permission:create $Name");
        Route::post("$Name/", [ClientController::class, 'store'])->name("$Name.store")->middleware("permission:create $Name");
        Route::post("$Name/store1", [ClientController::class, 'store1'])->name("$Name.store1")->middleware("permission:create $Name");
        Route::get("/$Name/{client}/edit", [ClientController::class, 'edit'])->name("$Name.edit")->middleware("permission:update $Name");
        Route::put("/$Name/{client}/", [ClientController::class, 'update'])->name("$Name.update")->middleware("permission:update $Name");
        Route::delete("/$Name/{client}/", [ClientController::class, 'destroy'])->name("$Name.destroy")->middleware("permission:delete $Name");

        // your existing units routes
        $Name = 'units';
        Route::get("$Name/", [UnitController::class, 'index'])->name("$Name.index")->middleware("permission:read $Name");
        Route::get("$Name/create", [UnitController::class, 'create'])->name("$Name.create")->middleware("permission:create $Name");
        Route::post("$Name/", [UnitController::class, 'store'])->name("$Name.store")->middleware("permission:create $Name");
        Route::get("/$Name/{unit}/edit", [UnitController::class, 'edit'])->name("$Name.edit")->middleware("permission:update $Name");
        Route::put("/$Name/{unit}/", [UnitController::class, 'update'])->name("$Name.update")->middleware("permission:update $Name");
        Route::get("/$Name/show/{unit}/", [UnitController::class, 'show'])->name("$Name.show")->middleware("permission:read $Name");
        Route::put("/$Name/{unit}/sale", [UnitController::class, 'sale'])->name("$Name.sale")->middleware("permission:update $Name");
        Route::delete("/$Name/{unit}/", [UnitController::class, 'destroy'])->name("$Name.destroy")->middleware("permission:delete $Name");
        Route::post("unitsfeature/", [UnitFeatureController::class, 'store'])->name("unitsfeature.store")->middleware("permission:create $Name");
        Route::delete("/unitsfeature/{unitfeature}/", [UnitFeatureController::class, 'destroy'])->name("unitsfeature.destroy")->middleware("permission:delete $Name");

        Route::get('units/by-building/{building}', function ($buildingId) {
            return \App\Models\Unit::where('building_id', $buildingId)
                ->select('id', 'name')
                ->get();
        })->name('units.byBuilding')->middleware("permission:read units");

        //services
        $Name = 'services';
        Route::get("$Name/", [ServiceController::class, 'index'])->name("$Name.index")->middleware("permission:read $Name");
        Route::get("$Name/create", [ServiceController::class, 'create'])->name("$Name.create")->middleware("permission:create $Name");
        Route::post("$Name/", [ServiceController::class, 'store'])->name("$Name.store")->middleware("permission:create $Name");
        Route::get("/$Name/{service}/edit", [ServiceController::class, 'edit'])->name("$Name.edit")->middleware("permission:update $Name");
        Route::put("/$Name/{service}/", [ServiceController::class, 'update'])->name("$Name.update")->middleware("permission:update $Name");
        Route::delete("/$Name/{service}/", [ServiceController::class, 'destroy'])->name("$Name.destroy")->middleware("permission:delete $Name");
        //contracts
        $Name = 'contracts';
        Route::get("$Name/", [ContractController::class, 'index'])->name("$Name.index")->middleware("permission:read $Name");
        Route::get("/$Name/{contract}/show", [ContractController::class, 'show'])->name("$Name.show")->middleware("permission:read $Name");
        Route::get("$Name/create", [ContractController::class, 'create'])->name("$Name.create")->middleware("permission:create $Name");
        Route::get("$Name/createbuilding", [ContractController::class, 'createbuildings'])->name("$Name.createbuilding")->middleware("permission:create $Name");
        Route::post("$Name/", [ContractController::class, 'store'])->name("$Name.store")->middleware("permission:create $Name");
        Route::post("$Name/building", [ContractController::class, 'storebuilding'])->name("$Name.storebuilding")->middleware("permission:create $Name");
        Route::get("/$Name/{contract}/edit", [ContractController::class, 'edit'])->name("$Name.edit")->middleware("permission:update $Name");
        Route::put("/$Name/{contract}/", [ContractController::class, 'update'])->name("$Name.update")->middleware("permission:update $Name");
        Route::put("/updatebuilding/{contract}/", [ContractController::class, 'updatebuilding'])->name("$Name.updatebuilding")->middleware("permission:update $Name");
        Route::delete("/$Name/{contract}/", [ContractController::class, 'destroy'])->name("$Name.destroy")->middleware("permission:delete $Name");
        Route::delete("/$Name/{contract}/cancellation", [ContractController::class, 'cancellation'])->name("$Name.cancellation")->middleware("permission:delete $Name");
        Route::post('/contract-services', [ContractServiceController::class, 'store'])->name('contract-services.store')->middleware("permission:update $Name");
        Route::post('/contract-addservices', [ContractServiceController::class, 'addDate'])->name('addDate-services.store')->middleware("permission:update $Name");
        Route::delete('contract-video-delete/{id}', [ContractController::class, 'deleteVideo'])->name('contract.video.delete');
        Route::delete('contract-image/{id}', [ContractController::class, 'deleteImage'])->name('contract.image.delete');



        Route::put('/contract-services/{contractService}', [ContractServiceController::class, 'update'])->name('contract-services.update')->middleware("permission:update $Name");
        Route::delete('/contract-services/{contractService}', [ContractServiceController::class, 'destroy'])->name('contract-services.destroy')->middleware("permission:delete $Name");
        Route::post('/unit-expenses', [UnitExpenseController::class, 'store'])->name('unit-expenses.store');
        Route::delete('/unit-expenses/{unitExpense}', [UnitExpenseController::class, 'destroy'])->name('unit-expenses.destroy');
        Route::get('/unit-expenses/{unitId}/offers', [UnitExpenseController::class, 'getOffers']);
        Route::post('/unit-expensesstoreInPage', [UnitExpenseController::class, 'storeInPage'])->name('unit-expenses.storeInPage');

        //invoices
        $Name = 'invoices';
        Route::get("$Name/", [InvoiceController::class, 'index'])->name("$Name.index")->middleware("permission:read $Name");
        Route::get("$Name/history", [InvoiceController::class, 'history'])->name("$Name.history")->middleware("permission:read $Name");
        Route::put("/$Name/{invoice}/", [InvoiceController::class, 'update'])->name("$Name.update")->middleware("permission:update $Name");
        Route::post("/pay-services", [InvoiceController::class, 'services'])->name("pay-services.store")->middleware("permission:update $Name");
        Route::post('/contracts/pay', [InvoiceController::class, 'payByContract'])->name('contracts.pay');

        // payment history with the invoices
        Route::post("$Name/{invoice}/payment-history", [InvoiceController::class, 'storePaymentHistory'])
            ->name("$Name.payment-history.store")
            ->middleware("permission:update $Name");
        Route::get("$Name/{invoice}/payment-history", [InvoiceController::class, 'showPaymentHistory'])
            ->name("$Name.payment-history.show")
            ->middleware("permission:read $Name");


        //expenseOffers
        $Name = 'expenseOffers';
        Route::get("$Name/", [ExpenseOfferController::class, 'index'])->name("$Name.index")->middleware("permission:read $Name");
        Route::get("$Name/create", [ExpenseOfferController::class, 'create'])->name("$Name.create")->middleware("permission:create $Name");
        Route::post("$Name", [ExpenseOfferController::class, 'store'])->name("$Name.store")->middleware("permission:create $Name");
        Route::get("/$Name/{expenseOffer}/edit", [ExpenseOfferController::class, 'edit'])->name("$Name.edit")->middleware("permission:update $Name");
        Route::put("/$Name/{expenseOffer}/", [ExpenseOfferController::class, 'update'])->name("$Name.update")->middleware("permission:update $Name");
        Route::delete("/$Name/{expenseOffer}", [ExpenseOfferController::class, 'destroy'])->name("$Name.destroy")->middleware("permission:delete $Name");
        Route::post('/expenseOffers/{expenseOffer}/update-status', [ExpenseOfferController::class, 'updateStatus'])->name('expenseOffers.updateStatus');
        Route::post('/expense-categories', [ExpenseCategoryController::class, 'store'])->name('expense-categories.store');
        Route::delete('/expense-categories/{id}', [ExpenseCategoryController::class, 'destroy'])->name('expense-categories.destroy');
        Route::get('/api/expense-categories', [ExpenseCategoryController::class, 'fetch'])->name('expense-categories.fetch');

        //search func
        Route::get('/units/search', [UnitController::class, 'search'])->name('units.search');
        Route::get('/clients/search', [ClientController::class, 'search'])->name('clients.search');


        //All Routes
        Route::get("logout", [AuthController::class, 'logout'])->name("logout");
        Route::get('/', function () {
            return redirect()->route('any');
        });
        Route::get('dashboard/{second}/{third}', [RoutingController::class, 'thirdLevel'])->name('third');
        Route::get('/dashboard', [RoutingController::class, 'root'])->name('any');
        Route::get('dashboard/{second}', [RoutingController::class, 'secondLevel'])->name('second');
    }
);
