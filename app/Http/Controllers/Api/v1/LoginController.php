<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    //
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function login(Request $request)
    {
        $login = $request->validate([
            "email" => "required|string",
            "password" => "required|string"
        ]);

        if (!Auth::attempt($login)) {
            return response()->json([
                "message" => __("Invalid credentials"),
                "data" => [],
                "result" => "error"
            ]);
        }
        $current_user = Auth::user();
        $token = $current_user->createToken('authToken', [$current_user->role->label])->accessToken;
        return response()->json([
            "message" => __("Login with success"),
            "data" => [
                'access_token' => $token,
                'user' => [
                    "name" => Auth::user()->name,
                    "email" => Auth::user()->email
                ]
            ],
            "result" => "error"
        ]);
    }
}
