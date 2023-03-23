<?php

namespace App\Http\Controllers;


use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
class AuthController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login','forgot_password','reset_password']]);
    }

    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login()
    {
        $credentials = request(['email', 'password']);

        if (! $token = auth()->attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $this->respondWithToken($token);
    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        return response()->json(auth()->user());
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        auth()->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->respondWithToken(auth()->refresh());
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60
        ]);
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
        ], 500);
}

}
