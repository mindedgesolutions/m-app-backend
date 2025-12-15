<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AdminResource;
use App\Models\OneTimePassword;
use App\Models\RefreshToken;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function adminLogin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|exists:users,email',
            'password' => 'required'
        ], [
            '*.required' => ':Attribute is required',
            'username.exists' => 'Username does not exist'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        try {
            DB::beginTransaction();

            if (!Auth::attempt(['email' => $request->username, 'password' => $request->password])) {
                return response()->json(['errors' => ['Incorrect credentials']], Response::HTTP_UNAUTHORIZED);
            }
            $user = Auth::user();
            $userData = User::findOrFail($user->id);
            $tokenResult = $user->createToken('AuthToken');
            $accessToken = $tokenResult->accessToken;
            $tokenModel = $tokenResult->token;
            $tokenModel->expires_at = now()->addSeconds(10);
            $expiresAt = $tokenModel->expires_at;
            $tokenModel->save();

            $refreshToken = Str::random(64);
            $name = config('lookup.REFRESH_TOKEN_NAME');

            RefreshToken::create([
                'user_id' => $user->id,
                'token' => hash('sha256', $refreshToken),
                'revoked' => false,
                'expires_at' => now()->addDays(30),
            ]);

            $oneTimeToken = Str::random(64);
            OneTimePassword::create([
                'user_id' => $user->id,
                'otp' => $oneTimeToken,
            ]);

            $responseData = [
                'data' => $user ? AdminResource::make($userData) : null,
                'token' => $accessToken,
                'token_type' => 'Bearer',
                'expires_in' => $expiresAt,
                'one_time_pass' => $oneTimeToken,
            ];

            DB::commit();

            return response()->json($responseData, Response::HTTP_OK)->cookie(
                $name,
                $refreshToken,
                60 * 24 * 30,
                '/',
                null,
                false,
                true,
                false,
                'Lax'
            );
        } catch (\Throwable $th) {
            Log::error(['signin error: ', $th->getMessage()]);
            DB::rollBack();
            return response()->json(['errors' => 'Something went wrong', Response::HTTP_INTERNAL_SERVER_ERROR]);
        }
    }

    // --------------------------------

    public function deleteOneTimePassword(Request $request, $token)
    {
        $check = OneTimePassword::where('otp', $token)->where('active', true)->first();
        if ($check) {
            OneTimePassword::where('otp', $token)->update(['active' => false]);
            return response()->json(['message' => 'success'], Response::HTTP_OK);
        } else {
            return response()->json(['errors' => ['Token not found']], Response::HTTP_UNAUTHORIZED);
        }
    }

    // --------------------------------

    public function refreshToken(Request $request)
    {
        try {
            DB::beginTransaction();

            $name = config('lookup.REFRESH_TOKEN_NAME');
            $refreshToken = $request->cookie($name);
            Log::info('Refresh token request with token: ' . $refreshToken);

            if (!$refreshToken) {
                return response()->json(['error' => 'Unauthenticated'], 401);
            }

            $stored = RefreshToken::where('token', hash('sha256', $refreshToken))
                ->where('revoked', false)
                ->where('expires_at', '>', now())
                ->first();

            if (!$stored) {
                return response()->json(['error' => 'Unauthenticated'], 401);
            }

            $user = User::find($stored->user_id);
            if (!$user) {
                return response()->json(['error' => 'Unauthenticated'], 401);
            }

            $tokenResult = $user->createToken('AuthToken');
            $accessToken = $tokenResult->accessToken;
            $tokenModel = $tokenResult->token;
            $tokenModel->expires_at = now()->addSeconds(10);
            $expiresAt = $tokenModel->expires_at;
            $tokenModel->save();

            $newRefreshToken = Str::random(64);
            $stored->update([
                'token'      => hash('sha256', $newRefreshToken),
                'revoked'    => false,
                'expires_at' => now()->addDays(30),
            ]);

            DB::commit();

            return response()->json([
                'data'        => AdminResource::make($user),
                'token'       => $accessToken,
                'token_type'  => 'Bearer',
                'expires_in'  => $expiresAt,
            ])->cookie(
                $name,
                $newRefreshToken,
                60 * 24 * 30,
                '/',
                null,
                false,
                true,
                false,
                'Lax'
            );
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('Token refresh error: ' . $th->getMessage());
            return response()->json(['error' => 'Token refresh failed'], 500);
        }
    }

    // --------------------------------

    public function logout(Request $request)
    {
        $name = config('lookup.REFRESH_TOKEN_NAME');
        $refreshToken = $request->cookie($name);
        $request->user()->token()->revoke();
        if ($refreshToken) {
            RefreshToken::where('token', hash('sha256', $refreshToken))
                ->where('revoked', false)
                ->update(['revoked' => true]);
            RefreshToken::where('user_id', $request->user()->id)->delete();
            OneTimePassword::where('user_id', $request->user()->id)->delete();
        }
        $cookieName = config('lookup.REFRESH_TOKEN_NAME');
        $forgetCookie = cookie()->forget($cookieName, '/', null, false, true, false, 'Lax');

        return response()->json(['message' => 'Logged out successfully'])
            ->withCookie($forgetCookie);
    }

    // --------------------------------

    public function me()
    {
        $user = Auth::user();
        $data = User::findOrFail($user->id);

        return response()->json(['data' => AdminResource::make($data)], Response::HTTP_OK);
    }
}
