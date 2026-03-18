<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthTokenController extends Controller
{
    public function store(Request $req)
    {
        $data = $req->validate([
            'email'       => 'required|email',
            'password'    => 'required|string',
            'device_name' => 'nullable|string'
        ]);

        if (!Auth::attempt(['email' => $data['email'], 'password' => $data['password']])) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $user = $req->user();
        // revoke old tokens for this device if you want 1-per-device
        $user->tokens()->where('name', $data['device_name'] ?? 'api')->delete();

        $token = $user->createToken($data['device_name'] ?? 'api')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user'  => ['id' => $user->id, 'email' => $user->email, 'name' => $user->name]
        ]);
    }
}
