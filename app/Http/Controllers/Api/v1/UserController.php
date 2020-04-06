<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Resources\User as UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\User;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function index()
    {
        return response()->json([
            "data" => [
                'items' => UserResource::collection(User::with('role')->get()),
            ],
            "message" => __("Got collection"),
            "success" => true
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return void
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            "name" => 'required|string',
            "phone_number" => 'required|string|max:10|unique:users',
            "email" => 'sometimes|email|unique:users',
            "role_type_id" => 'required',
            'password' => 'required|string'
        ]);

        $u = new User();
        $u->fill([
            "name" => $request->post('name'),
            "phone_number" => $request->post('phone_number'),
            "email" => $request->post('email'),
            "role_type_id" => $request->post('role_type_id'),
            "password" => Hash::make($request->post('password')),
        ]);

        $success = $u->save();

        if ($success) {
            return response()->json([
                "data" => [
                    'item' => new UserResource(User::where(['id' => $u->id])->with("role")->first())
                ],
                "message" => __("New user created"),
                "success" => true
            ]);
        }

        return response()->json([
            "error" => __("Creation failed"),
            "success" => false
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(Request $request, $id)
    {

        $request->validate([
            "name" => 'required|string',
            "phone_number" => 'required|string|max:10|unique:users',
            "email" => 'sometimes|email|unique:users',
            "role_type_id" => 'required'
        ]);

        $u = User::find($id);

        $u->name = $request->post('name');
        $u->phone_number = $request->post('phone_number');
        $u->email = $request->post('email');
        $u->role_type_id = $request->post('role_type_id');
        if ($request->post('password')) {
            $u->password = Hash::make($request->post('password'));
        }

        $success = $u->save();

        if ($success) {
            return response()->json([
                "data" => [
                    'item' => new UserResource($u)
                ],
                "message" => __("User updated"),
                "success" => true
            ]);
        }

        return response()->json([
            "error" => __("Update failed"),
            "success" => false
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return Response
     */
    public function softDelete($id)
    {
        $user = User::find($id);
        $done = $user->delete();

        if ($done) {
            return response()->json([
                'message' => __("Delete success"),
                'success' => true
            ]);
        } else {
            return response()->json([
                "error" => __("Delete failed"),
                "success" => false
            ]);
        }
    }
}
