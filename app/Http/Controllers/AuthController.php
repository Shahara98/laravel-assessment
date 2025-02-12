<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Validator;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name'  => 'required|string|max:255',
            'email'      => 'required|email|unique:users',
            'password'   => 'required|min:6'
        ]);
        
        if ($validator->fails()){
            return response()->json(['errors' => $validator->errors()], 422);
        }
        
        $user = User::create([
            'first_name' => $request->first_name,
            'last_name'  => $request->last_name,
            'email'      => $request->email,
            'password'   => Hash::make($request->password)
        ]);
        
        $token = $user->createToken('AuthToken')->accessToken;
        
        return response()->json(['token' => $token], 201);
    }
    
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');
        if (Auth::attempt($credentials)) {
            $user  = Auth::user();
            $token = $user->createToken('AuthToken')->accessToken;
            return response()->json(['token' => $token], 200);
        }
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    public function logout(Request $request)
    {
        $request->user()->token()->revoke();
        return response()->json(['message' => 'Successfully logged out'], 200);
    }
}
