<?php

namespace App\Http\Controllers;

use Tymon\JWTAuth\Facades\JWTAuth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
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
        if (JWTAuth::user()->is_active != 1) {
            return response()->json(['error' => 'Unauthorized', 'message' => "User Inactive!"], 401);
        }
        JWTAuth::user()->ip = $request->ip();
        JWTAuth::user()->save();
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
