<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use App\User;
use Auth;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()], 401);
        }
        $user = $request->all();
        $user['password'] = bcrypt($user['password']);
        $user = User::create($user);

        $token = $user->createToken('API Token')->accessToken;

        return response()->json([ 'user' => $user, 'token' => $token], 200);
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'email|required',
            'password' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()], 401);
        }

        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            $user = $request->user();
            $token = $user->createToken('API Token')->accessToken;
            return response(['user' => $user, 'token' => $token]);
        }

        return response()->json(['error'=>'No User found !'], 401);
    }

    public function userDetail()
    {
        $user = Auth::user();

        return response()->json(['user' => $user], 200);
    }
}
