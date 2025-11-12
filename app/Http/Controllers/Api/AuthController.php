<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Client;
use App\Models\Driver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Admin Login
     * POST /api/auth/admin/login
     */
    public function adminLogin(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        // Check if user has admin role
        if (!$user->hasRole('Admin')) {
            throw ValidationException::withMessages([
                'email' => ['You do not have admin access.'],
            ]);
        }

        // Create token
        $token = $user->createToken('admin-token', ['admin'])->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Admin login successful',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'roles' => $user->getRoleNames(),
                ],
                'token' => $token,
                'token_type' => 'Bearer',
            ],
        ]);
    }

    /**
     * Client Registration
     * POST /api/auth/client/register
     */
    public function clientRegister(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'business_name' => 'nullable|string|max:255',
            'email' => 'required|email|unique:users,email|unique:clients,email',
            'password' => 'required|string|min:8|confirmed',
            'mobile' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'preferred_city' => 'nullable|string|max:255',
            'currency_code' => 'nullable|string|max:3',
        ]);

        // Create user account
        $user = User::create([
            'name' => $validated['first_name'] . ' ' . ($validated['last_name'] ?? ''),
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'is_active' => true,
        ]);

        // Assign client role
        $user->assignRole('Client');

        // Create client record
        $client = Client::create([
            'user_id' => $user->id,
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'] ?? '',
            'business_name' => $validated['business_name'] ?? $validated['first_name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'address' => $validated['address'] ?? '',
            'preferred_city' => $validated['preferred_city'] ?? '',
            'currency_code' => $validated['currency_code'] ?? 'USD',
            'credit_limit' => 0,
            'open_balance' => 0,
            'available_credit' => 0,
            'total_order_amount' => 0,
            'rewards' => 0,
        ]);

        // Create token
        $token = $client->createToken('client-token', ['client'])->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Client registration successful',
            'data' => [
                'client' => [
                    'id' => $client->id,
                    'first_name' => $client->first_name,
                    'last_name' => $client->last_name,
                    'business_name' => $client->business_name,
                    'email' => $client->email,
                    'currency_code' => $client->currency_code,
                    'credit_limit' => $client->credit_limit,
                    'available_credit' => $client->available_credit,
                ],
                'token' => $token,
                'token_type' => 'Bearer',
            ],
        ], 201);
    }

    /**
     * Client Login
     * POST /api/auth/client/login
     */
    public function clientLogin(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $client = Client::where('email', $request->email)->first();

        if (!$client || !Hash::check($request->password, $client->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        // Create token
        $token = $client->createToken('client-token', ['client'])->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Client login successful',
            'data' => [
                'client' => [
                    'id' => $client->id,
                    'first_name' => $client->first_name,
                    'last_name' => $client->last_name,
                    'business_name' => $client->business_name,
                    'email' => $client->email,
                    'currency_code' => $client->currency_code,
                    'credit_limit' => $client->credit_limit,
                    'available_credit' => $client->available_credit,
                ],
                'token' => $token,
                'token_type' => 'Bearer',
            ],
        ]);
    }

    /**
     * Driver Login
     * POST /api/auth/driver/login
     */
    public function driverLogin(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $driver = Driver::where('email', $request->email)->first();

        if (!$driver || !Hash::check($request->password, $driver->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        // Create token
        $token = $driver->createToken('driver-token', ['driver'])->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Driver login successful',
            'data' => [
                'driver' => [
                    'id' => $driver->id,
                    'driver_id' => $driver->driver_id,
                    'first_name' => $driver->first_name,
                    'last_name' => $driver->last_name,
                    'email' => $driver->email,
                    'mobile' => $driver->mobile,
                    'client_id' => $driver->client_id,
                ],
                'token' => $token,
                'token_type' => 'Bearer',
            ],
        ]);
    }

    /**
     * Logout (for all user types)
     * POST /api/auth/logout
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully',
        ]);
    }

    /**
     * Get authenticated user/client/driver details
     * GET /api/auth/me
     */
    public function me(Request $request)
    {
        $user = $request->user();
        
        // Check which type of user
        if ($user instanceof User) {
            return response()->json([
                'success' => true,
                'data' => [
                    'type' => 'admin',
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'roles' => $user->getRoleNames(),
                    ],
                ],
            ]);
        } elseif ($user instanceof Client) {
            return response()->json([
                'success' => true,
                'data' => [
                    'type' => 'client',
                    'client' => [
                        'id' => $user->id,
                        'first_name' => $user->first_name,
                        'last_name' => $user->last_name,
                        'business_name' => $user->business_name,
                        'email' => $user->email,
                        'currency_code' => $user->currency_code,
                        'credit_limit' => $user->credit_limit,
                        'available_credit' => $user->available_credit,
                    ],
                ],
            ]);
        } elseif ($user instanceof Driver) {
            return response()->json([
                'success' => true,
                'data' => [
                    'type' => 'driver',
                    'driver' => [
                        'id' => $user->id,
                        'driver_id' => $user->driver_id,
                        'first_name' => $user->first_name,
                        'last_name' => $user->last_name,
                        'email' => $user->email,
                        'mobile' => $user->mobile,
                        'client_id' => $user->client_id,
                    ],
                ],
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'User type not recognized',
        ], 400);
    }
}
