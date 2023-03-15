<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function register()
    {
        request()->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|confirmed'
        ]);
        $password = Hash::make(request('password'));
        $credentials = [
            'name' => request('name'),
            'email' => request('email'),
            'password' => $password
        ];

        $user = User::create($credentials);

        if(!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'User could not be created'
            ], 500);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'User created successfully'
        ], 201);
    }
}

 