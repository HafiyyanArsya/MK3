<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;


class AuthController extends Controller
{
    // Fungsi register untuk registrasi user baru
    public function register(Request $request)
    {
        try {
            // Validasi input
            $validated = $request->validate([
                'name' => 'required|string|max:255|regex:/^[a-zA-Z\s]+$/',
                'email' => 'required|string|email|unique:users',
                'password' => 'required|string|min:8',
            ]);

            // Buat user baru dengan password yang di-hash
            $user = User::create([
                'name' => trim($validated['name']),
                'email' => trim($validated['email']),
                'password' => Hash::make(trim($validated['password'])), // Gunakan Hash::make()
            ]);

            // Generate token
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'status' => 'success',
                'message' => 'User registered successfully',
                'data' => [
                    'token' => $token,
                    'user' => $user,
                ],
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Registration failed. Please try again.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // Fungsi login untuk autentikasi user
    public function login(Request $request)
    {
        try {
            // Validasi input
            $request->validate([
                'email' => 'required|string|email',
                'password' => 'required|string|min:8',
            ]);

            $credentials = $request->only('email', 'password');

            // Cek apakah user ada dan password cocok
            if (!Auth::attempt($credentials)) {
                return response()->json(['message' => 'Unauthorized'], 401);
            }

            // Ambil user setelah autentikasi berhasil
            $user = Auth::user();

            // Buat token baru
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'status' => 'success',
                'message' => 'Login successful',
                'data' => [
                    'token' => $token,
                    'user' => $user,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Login failed. Please try again.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // Fungsi logout
    public function logout(Request $request)
    {
        try {
            // Pastikan user sudah login sebelum logout
            if (!$request->user()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No authenticated user found.',
                ], 401);
            }

            // Hapus semua token user saat ini
            $request->user()->tokens()->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Logout successful',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Logout failed. Please try again.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // Fungsi mendapatkan data user yang sedang login
    public function userProfile(Request $request)
    {
        return response()->json([
            'status' => 'success',
            'user' => $request->user(),
        ]);
    }
}