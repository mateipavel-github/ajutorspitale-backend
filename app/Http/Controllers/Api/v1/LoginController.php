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

        $login = request()->input('login_identifier');
        $field = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone_number';
        request()->merge([$field => $login]);

        $login = $request->validate([
            $field => 'required|string',
            'password' => 'required|string'
        ]);

        if (!Auth::attempt($login)) {
            return response()->json([
                "message" => __("Invalid credentials"),
                "data" => [],
                'success' => false
            ]);
        }
        $current_user = Auth::user();
        $token = $current_user->createToken('authToken', [$current_user->role->slug])->accessToken;
        return response()->json([
            "message" => __("Login with success"),
            "data" => [
                'access_token' => $token,
                'user' => [
                    "id" => Auth::user()->id,
                    "name" => Auth::user()->name,
                    "email" => Auth::user()->email,
                    "role" => ['slug'=>Auth::user()->role->slug, 'id'=>Auth::user()->role->id]
                ]
            ],
            'success' => true
        ]);
    }

}
