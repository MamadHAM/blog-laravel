<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;


class UserController extends Controller
{
    public function register(Request $request)
    {
        $data = $request->all();

        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'email' => 'required|email|unique:users',
            'password' => 'required|confirmed|min:5'
        ]);

        if ($validator->fails()) {
            return response()->json(["msg"=>$validator->errors()->first()], 422);
        }

        $data['password'] = bcrypt($request->password);
        $data['role_id'] = "2"; //--> 2: User

        $user = User::create($data);

        $accessToken = $user->createToken('UserToken')->accessToken;
        return response()->json([
            'email' => $data['email'],
            'token' => $accessToken,
            'token_type' => 'Bearer'
        ]);
    }

    public function login(Request $request)
    {
        $data = $request->all();

        $validator = Validator::make($request->all(), [
            'email' => 'email|required',
            'password' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(["msg"=>$validator->errors()->first()], 422);
        }

        if (!auth()->attempt($data)) {
            return response()->json(["msg"=>__('auth.login_failed')], 422);
        }

        Auth::user()->tokens->each(function($token, $key) {
            $token->delete();
        });

        $user = auth()->user();
        $tokenResult = $user->createToken('userToken');
        $tokenModel = $tokenResult->token;
        if ($request->remember_me)
            $tokenModel->expires_at = Carbon::now()->addWeeks(1);
        $tokenModel->save();
        return response()->json([
            'user' => new UserResource($user),
            'token' => $tokenResult->accessToken,
            'token_type' => 'Bearer'
        ]);

    }

    public function logout(Request $request)
    {
        /** @var User $user
         */
        $request->user()->token()->revoke();
        return response()->json(["msg"=>__("auth.logout_success")]);
    }

    public function profile()
    {
        $user = auth()->user();

        return response()->json($user, 200);
    }

}
