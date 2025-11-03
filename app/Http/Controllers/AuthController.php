<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    /**
     * Register a new user.
     * 
     * @param  \Illuminate\Http\Request  $request
     * $name - required, string name of the user
     * $email - required, string email of the user
     * $password - required, string password of the user
     * $profile_photo - optional, file profile photo of the user
     * @return \Illuminate\Http\JsonResponse
     * 
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg|max:5048',
            'password' => 'required|string|min:8|max:255',
        ]);

        if ($validator->fails()) {
            return $this->sendErrorApiResponse($validator->errors(), 422, $validator->errors());
        }

        $validated = $validator->validated();

        try {

            if ($request->hasFile('profile_photo')) {
                $file = $request->file('profile_photo');
                $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('profile_photos', $filename, 'public');

                $validated['profile_photo_url'] = 'storage/' . $path;
            }

            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'profile_photo_url' => $validated['profile_photo_url'] ?? null,
            ]);
        } catch (\Exception $e) {
            return $this->sendErrorApiResponse('User registration failed.', 500);
        }

        return $this->sendSuccessApiResponse('User registered successfully.', $user, 201);
    }

    /**
     * Login user and create token.
     * 
     * @param  \Illuminate\Http\Request  $request
     * $email - required, string email of the user
     * $password - required, string password of the user
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'password' => 'required|string|min:8|max:255',
        ]);

        if ($validator->fails()) {
            return $this->sendErrorApiResponse($validator->errors(), 422, $validator->errors());
        }

        // $user = User::where('email', $validated['email'])->first();
        $input = $request->only('email', 'password');
        if(!Auth::attempt($input)) {
            return $this->sendErrorApiResponse('Invalid email or password.', 401);
        }
        $user = Auth::user();
        $user = User::find($user->id);
        $token = $user->createToken('auth_token')->plainTextToken;
        return $this->sendSuccessApiResponse('User logged in successfully.', [
            'token_type' => 'Bearer',
            'access_token' => $token,
            'user' => $user,
        ]);
    }

    /**
     * Get the logged in user.
     * 
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function getLoginUser()
    {
        $user = Auth::user();
        if (!$user) {
            return $this->sendErrorApiResponse('User not found.', 404);
        }
        return $this->sendSuccessApiResponse('User retrieved successfully.', $user);
    }

    /**
     * Logout user (Revoke the token).
     * 
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function logout(Request $request)
    {
        $user = Auth::user();

        $request->user()->currentAccessToken()->delete();

        return $this->sendSuccessApiResponse('User logged out successfully.', null);

    }

}
