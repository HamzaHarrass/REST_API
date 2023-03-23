<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

class UserController extends Controller
{
    public function register()
    {
        request()->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|confirmed'
        ]);

        $user = User::create([
            'name' => request('name'),
            'email' => request('email'),
            'password' => Hash::make(request('password')),
        ]);


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


public function update(Request $request, User $user)
{
    $data =[];

    if($request->input('password')){
        $data['password']= $request->input('password');
    }
    if($request->input('name')){
        $data['name']= $request->input('name');
    }
    if($request->input('email')){
        $data['email']= $request->input('email');
    }

    $user = JWTAuth::user();

     try {
        $user->where('id', $user->id)->update($data);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()]);
    }

    // $user->users()->sync($user);

    return response()->json([
        'success'=>'user has been update',
        'data' => ['user' => $user]
    ], 201);
}

    


}

