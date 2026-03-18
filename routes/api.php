<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use App\Models\User;
use Illuminate\Validation\ValidationException;
use App\Http\Controllers\Api\ApiLandController;
use App\Http\Controllers\Api\ApiUnitController;
use App\Http\Controllers\Api\ApiBuildingController;
use App\Http\Controllers\Api\ApiDashboardController;
use App\Http\Controllers\Api\DashboardController;






Route::post('/login-token', function (Request $request) {
    $data = $request->validate([
        'email' => ['required', 'email'],
        'password' => ['required', 'string'],
        'device_name' => ['nullable', 'string'] // e.g. "android-app"
    ]);

    $user = User::where('email', $data['email'])->first();
    if (!$user || !Hash::check($data['password'], $user->password)) {
        throw ValidationException::withMessages(['email' => 'The provided credentials are incorrect.']);
    }

 
    $token = $user->createToken($data['device_name'] ?? 'api')->plainTextToken;

    return response()->json([
        'token' => $token,
        'user'  => ['id' => $user->id, 'email' => $user->email, 'name' => $user->name],
    ]);
});
/* ---- Protected API ---- */
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/dashboard/summary', [ApiDashboardController::class, 'summary']);


    Route::get('/buildings', [ApiBuildingController::class, 'index']);
    Route::get('/buildings/{building}', [ApiBuildingController::class, 'show']);
    Route::get('/buildings/{building}/units', [ApiBuildingController::class, 'units']); // <-- add this

    Route::get('/units', [ApiUnitController::class, 'index']);
    Route::get('/units/{unit}', [ApiUnitController::class, 'show']);

    Route::get('/lands', [ApiLandController::class, 'index']);
    Route::get('/lands/{land}', [ApiLandController::class, 'show']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/dashboard/summary', [DashboardController::class, 'summary']);
});


Route::middleware('auth:sanctum')->get('/me', function (Request $request) {
    return $request->user();
});

Route::middleware('auth:sanctum')->post('/logout-token', function (Request $request) {
    /** @var \Laravel\Sanctum\PersonalAccessToken|\Laravel\Sanctum\TransientToken|null $token */
    $token = $request->user()->currentAccessToken();

    // Works at runtime and keeps the linter happy
    if ($token && method_exists($token, 'delete')) {
        $token->delete();
    }

    return response()->json(['ok' => true]);
});


Route::middleware('auth:sanctum')->post('/logout-all', function (Request $request) {
    $request->user()->tokens()->delete();
    return response()->json(['ok' => true]);
});

Route::get('/ping', fn() => response()->json(['pong' => true]));
