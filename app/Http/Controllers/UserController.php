<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user = User::latest()->get();
        $data = [
            'success' => true,
            'message' => 'List User',
            'data' => $user
        ];
        return response()->json($data, 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
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

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($user_id)
    {
        $user = User::find($user_id);
        if ($user) {
            return response()->json([
                'success' => true,
                'message' => 'Detail user',
                'data'    => $user,
            ], 200);
        }

        return response()->json([
            'success' => false,
            'message' => "Data Not Found"
        ], 404);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $user_id)
    {
        $user = User::find($user_id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => "Data Not Found"
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'username' => 'required|min:3|max:255|unique:users,username,' . $user_id . ',user_id',
            'email' => 'required|email:rfc,dns|unique:users,email,' . $user_id . ',user_id',
            'is_active' => 'required',
            'role_id'   => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()
            ], 422);
        }

        $user->username = $request->username;
        $user->email = $request->email;
        if ($request->password) {
            $user->password = Hash::make($request->password, ['rounds' => 12]);
        }
        $user->is_active = $request->is_active;
        $user->role_id = $request->role_id;
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Berhasil update user!',
            'data'    => $user,
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($user_id)
    {
        $user = User::find($user_id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => "Data Not Found"
            ], 404);
        }

        $user->delete();
        return response()->json([
            'success' => true,
            'message' => 'Deleted Successfully!',
            'data'    => $user,
        ], 200);
    }
}
