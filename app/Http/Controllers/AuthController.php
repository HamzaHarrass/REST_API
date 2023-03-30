<?php

namespace App\Http\Controllers;

use Exception;
use Carbon\Carbon;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Claims\JwtId;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;


class AuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'register','reset','resetPassword']]);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);
        $credentials = $request->only('email', 'password');

        $token = Auth::attempt($credentials);
        if (!$token) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized',
            ], 401);
        }

        $user = Auth::user();
        return response()->json([
            'status' => 'success',
            'user' => $user,
            'authorisation' => [
                'token' => $token,
                'type' => 'bearer',
            ]
        ]);
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
            'password_confirmation' => 'required|same:password',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);
        return response()->json(['success' => 'register successfully'], 200);
    }

    public function logout()
    {
        Auth::logout();
        return response()->json([
            'status' => 'success',
            'message' => 'Successfully logged out',
        ]);
    }

    public function refresh()
    {
        return response()->json([
            'status' => 'success',
            'user' => Auth::user(),
            'authorisation' => [
                'token' => Auth::refresh(),
                'type' => 'bearer',
            ]
        ]);
    }
    public function me()
    {
        return response()->json(auth()->user());
    }

    public function forgot_password(Request $request){
        $request->validate([
            'email' => 'required|email|exists:users',
        ]);

        $response = Password::sendResetLink($request->only('email'));

        return $response == Password::RESET_LINK_SENT
            ? response()->json([
                'status' => 'success',
                'message' => 'password reset link has been sent to your email',
            ])
            : response()->json([
                'status' => 'error',
                'message' => 'unable to send password reset link. Please try again later',
            ], 500);
    }

    public function reset_password(Request $request)
    {
        $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email'],
            'password' => ['required', 'min:8', 'confirmed'],
        ]);

        $response = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ]);
                $user->save();
            }
        );

        return $response == Password::PASSWORD_RESET

            ? response()->json([
                'status' => 'success',
                'message' => 'password has been reset it successfully',
            ])
            : response()->json([
                'status' => 'error',
                'message' => 'failed when reset password',
                'error'=>Password::PASSWORD_RESET,
            ], 500);
    }

    public function updateProfile(Request $request){
        $request->validate([
            'email' => 'required|string|email|max:255',
            'password' => 'required|string|min:6',
            'new_email' => 'required|string|email|max:255|',
            'new_password' => 'required|string|min:6',
            'password_confirmation' => 'required|same:new_password',
        ]);
        $data =[
            'email'=>$request->new_email,
            'password'=>Hash::make($request->new_password),
        ];
        $user = JWTAuth::user();
        if(Auth::user()->email=== request('email') && Hash::check(request('password'), Auth::user()->password)){
            try{
                $user->where('id',$user->id)->update($data);
            }catch(Exception $e){
                return response()->json(['error'=>$e->getMessage()]);
            }
            return response()->json([
                'success'=>'user has been update',
                'data' => ['user' => User::find($user->id)]
            ], 201);
        }

            return response()->json(['error'=>'check your email or password']);
    }
}
