<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Exception;

class ManageProfileController extends Controller
{
public function store(Request $request)
{
    try {
        // ✅ Validate input
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'role' => 'required|string|exists:roles,name',
            'status' => 'nullable|in:active,inactive,pending',
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                'unique:admins,email',
                'regex:/^[\w.\-]+@[a-zA-Z0-9\-]+\.[a-zA-Z]{2,}$/'
            ],
            'password' => 'required|string|min:8|confirmed',
        ]);

        // ✅ Handle image upload
        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('admin', 'public');
        }

        // ✅ Create user without the 'role' column
        $user = Admin::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'address' => $validated['address'] ?? null,
            'status' => $validated['status'] ?? 'active',
            'image' => $imagePath,
            'password' => Hash::make($validated['password']),
        ]);

        // ✅ Assign the role using Spatie's method
        $user->assignRole($validated['role']);

        // ✅ Success response
        return response()->json([
            'message' => 'User registered successfully.',
            'data' => [
                "type" => 'user',
                "name" => $user->name,
                "email" => $user->email,
                "status" => $user->status,
                "phone" => $user->phone,
                "address" => $user->address,
                "role" => $user->roles->first()->name ?? null, // Get the assigned role's name
                "image" => $user->image ? asset('storage/' . $user->image) : null,
            ],
        ], 201);
    } catch (Exception $e) {
        return response()->json([
            'message' => 'Something went wrong.',
            'error' => $e->getMessage(),
        ], 500);
    }
}
}
