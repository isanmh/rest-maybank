<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthJwtController extends Controller
{
    // registrasi user
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|confirmed|min:6'
        ]);
        // buat user
        $user = User::create([
            'name' => $request['name'],
            'email' => $request['email'],
            'password' => Hash::make($request['password']),
        ]);

        return response()->json([
            'status' => 201,
            'message' => 'User berhasil didaftarkan',
            'data' => $user
        ], Response::HTTP_CREATED);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);
        // jwt token
        $token = JWTAuth::attempt([
            'email' => $request['email'],
            'password' => $request['password']
        ]);

        // cek jika token kosong
        if (!empty($token)) {
            return response()->json([
                'status' => Response::HTTP_OK,
                'message' => 'User berhasil login',
                'token' => $token,
                'type' => 'Bearer (JWT)'
            ]);
        }
        return response()->json([
            'status' => Response::HTTP_UNAUTHORIZED,
            'message' => 'Invalid credentials',
        ], Response::HTTP_UNAUTHORIZED);
    }
}
