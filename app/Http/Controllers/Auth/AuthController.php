<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\BaseController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends BaseController
{
    public function __construct() {
        $this->middleware('auth:api', ['except' => ['login', 'store']]);
    }

    public function store(Request $request, User $user){

        if (!Auth::user()->hasPermission('create_user')) {
            return $this->handleResponseErros(null, 'Unauthorized')->setStatusCode(403);
        }
        $request->validate([
            'name' => 'required|string|between:2,100',
            'email' => 'required|string|email|max:100|unique:users',
            'password' => 'required|string|min:6',
        ]);

        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = bcrypt($request->password);
        $user->save();
        $user->roles()->sync([2]);

        return $this->handleResponseSuccess($user, 'Create Editor Successfully!');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        $cerrent = $request->only('email', 'password');

        if (!$token = auth()->attempt($cerrent)) {
            return $this->handleResponseSuccess([], 'Unauthorized')->setStatusCode(401);
        }

        return $this->createNewToken($token);
    }

    public function refresh() {
        return $this->createNewToken(auth()->refresh());
    }

    public function logout() {
        auth()->logout();
        return response()->json(['message' => 'User successfully signed out']);
    }

    protected function createNewToken($token){
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'user' => auth()->user()
        ]);
    }
}
