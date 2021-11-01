<?php

namespace App\Http\Controllers;

use Tymon\JWTAuth\Facades\JWTAuth;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{

    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|min:3|max:255|unique:users,username',
            'email' => 'required|email:rfc,dns|unique:users,email',
            'password' => 'required|confirmed|min:5|max:255',
            'role_id'   => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $user = User::create([
            'username'      => $request->username,
            'password'      => Hash::make($request->password, ['rounds' => 12]),
            'email'         => $request->email,
            'ip'            => $request->ip(),
            'role_id'       => $request->role_id,
        ]);

        if ($user) {
            return response()->json([
                'success' => true,
                'user'    => $user,
            ], 201);
        }

        return response()->json([
            'success' => false,
        ], 409);
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|exists:users,username',
            'password' => 'required'
        ], [
            'username.exists' => "Username atau Password Salah!"
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        $credentials = request(['username', 'password']);

        if (!$token = JWTAuth::claims([
            'ip' => $request->ip(),
            'useragent' => $request->header('user-agent')
        ])->attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $this->respondWithToken($token);
    }

    public function me()
    {
        return response()->json(JWTAuth::user());
    }

    public function logout()
    {
        JWTAuth::invalidate(JWTAuth::getToken());
        return response()->json(['message' => 'Successfully logged out']);
    }

    public function refresh()
    {
        return $this->respondWithToken(JWTAuth::refresh());
    }

    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => JWTAuth::factory()->getTTL() * 60,
            'username' => JWTAuth::user()->username,
            'role_id' => JWTAuth::user()->role_id,
        ]);
    }
}
