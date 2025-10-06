<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Exceptions\JWTException;
use App\Http\Controllers\Controller;
use App\Mail\ForgetPassword;
use Illuminate\Support\Facades\Mail;
use App\Mail\RegisterOtpEmail;


class AuthController extends Controller
{

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required','string','email','max:255','unique:users',
                'regex:/^[\w\.\-]+@[a-zA-Z0-9\-]+\.[a-zA-Z]{2,}$/'
            ],
            'password' => 'required|string|min:8|confirmed',
        ]);


        // Create user
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        // Generate random 4-digit OTP
        $otp = rand(1000, 9999);
        $otp_expires_at = now()->addMinutes(10);

        // Save it with the user (make sure you have an otp column in users table)
        $user->otp = $otp;
        $user->otp_expires_at = $otp_expires_at;
        $user->save();

        // Send welcome email with OTP
        Mail::to($user->email)->send(new RegisterOtpEmail($user));

        return response()->json([
            'message' => 'User registered successfully. Please check your email for the OTP to verify your account.',
            'data' => [
                "type" => 'user',
                "name" => $user->name,
                "email" => $user->email,
            ],
        ], 201);
    }



    public function verifyotp(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|string|email|max:255',
            'otp'   => 'required|digits:4',
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        if ($user->is_otp_verified) {
            return response()->json(['message' => 'Account already verified.'], 200);
        }

        if (!$user->otp || !$user->otp_expires_at) {
            return response()->json(['error' => 'No pending OTP verification for this user.'], 400);
        }

        if ($user->otp != $validated['otp']) {
            return response()->json(['error' => 'Invalid OTP'], 400);
        }

        if (now()->greaterThan($user->otp_expires_at)) {
            return response()->json(['error' => 'OTP has expired'], 400);
        }

        // Mark user as verified
        $user->update([
            'is_otp_verified' => true,
            'status' => 'active',
            'otp' => null,
            'otp_expires_at' => null,
        ]);

        return response()->json(['message' => 'OTP verified successfully'], 200);
    }


    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        $guards = ['admin', 'api'];

        try {
            foreach ($guards as $guard) {
                if ($token = Auth::guard($guard)->attempt($credentials)) {
                    $user = Auth::guard($guard)->user();

                    // --- START OF FIX ---
                    if ($guard === 'api') {
                        // Regular API user
                        $type = 'user';
                    } else {
                        // Admin guard users (super_admin, manager, cashier, etc.)
                        // Retrieve the specific role name from the Spatie package
                        $roleName = $user->getRoleNames()->first();
                        $type = $roleName ?? $guard; // Use the specific role name, fallback to 'admin'
                    }
                    // --- END OF FIX ---


                    // For regular users, check if their account is verified
                    if ($type === 'user' && ($user->status === 'pending' || !$user->is_otp_verified)) {
                        Auth::guard($guard)->logout(); // Log out the unverified user
                        return response()->json(['message' => 'Account is not verified. Please verify your OTP.'], 403);
                    }

                    return response()->json([
                        'message' => 'Successfully logged in',
                        'data' => [
                            "type" => $type, // Now uses the specific role name (manager, cashier, etc.)
                            "name" => $user->name,
                            "email" => $user->email,
                        ],
                        'token' => $token,
                    ]);
                }
            }
        } catch (JWTException $e) {
            return response()->json(['error' => 'Could not create token'], 500);
        }

        return response()->json(['error' => 'Invalid credentials'], 401);
    }



    public function forgetpassword(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|string|email|max:255',
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        // Generate 4-digit OTP or token
        $otp = rand(1000, 9999);
        $otp_expires_at = now()->addMinutes(10); // valid for 10 minutes

        // Save OTP and expiry in user record
        $user->otp = $otp;
        $user->otp_expires_at = $otp_expires_at;
        $user->save();

        // Send OTP via email
        Mail::to($user->email)->send(new ForgetPassword($user));

        return response()->json([
            'message' => 'Password reset OTP sent to your email. It is valid for 10 minutes.'
        ], 200);
    }

    public function resetpassword(Request $request)
    {
        // Validate input
        $validated = $request->validate([
            'email' => 'required|string|email|max:255',
            'otp' => 'required|digits:4',
            'password' => 'required|string|min:8|confirmed',
        ]);

        // Find the user by email
        $user = User::where('email', $validated['email'])->first();

        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        if (!$user->otp || !$user->otp_expires_at) {
            return response()->json(['error' => 'No pending password reset for this user.'], 400);
        }

        if ($user->otp != $validated['otp']) {
            return response()->json(['error' => 'Invalid OTP'], 400);
        }

        if (now()->greaterThan($user->otp_expires_at)) {
            return response()->json(['error' => 'OTP has expired'], 400);
        }

        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        // Update password
        $user->update([
            'password' => Hash::make($validated['password']),
            'otp' => null,
            'otp_expires_at' => null,
        ]);

        return response()->json(['message' => 'Password reset successfully'], 200);
    }
}
