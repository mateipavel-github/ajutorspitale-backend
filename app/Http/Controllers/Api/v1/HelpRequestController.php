<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\HelpRequest;
use App\Http\Resources\HelpRequestCollection;
use \App\Http\Resources\HelpRequest as HelpRequestResource;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\User as UserResource;
use Illuminate\Support\Facades\Log;

class HelpRequestController extends Controller
{
    protected $per_page = 20;

    public function massAssignToCurrentUser(Request $request)
    {
        $howMany = $request->post('howMany');
        $userId = $request->user('api')->id;

        $requestIds = HelpRequest::whereIn('status', [1, 2])->whereNull('assigned_user_id')->pluck('id')->take($howMany);
        HelpRequest::whereIn('id', $requestIds)->update(array('assigned_user_id' => $userId));

        return ['success' => true];
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request)
    {
        
        $list = HelpRequest::select("*");
        if ($request->get("per_page")) {
            $this->per_page = $request->get('per_page');
        }

        if ($request->get("user_id")) {
            $list->where(['user_id' => $request->get("user_id")]);
        }

        if ($request->get("assigned_user_id")) {
            if (strtolower($request->get("assigned_user_id")) === "null") {
                $list->whereNull('assigned_user_id');
            } else {
                $list->where(['assigned_user_id' => $request->get("assigned_user_id")]);
            }
        }

        if ($request->get("medical_unit_id")) {
            $list->where(['medical_unit_id' => $request->get("medical_unit_id")]);
        }

        if ($request->get("medical_unit_type_id")) {
            $list->where(['medical_unit_type_id' => $request->get("medical_unit_type_id")]);
        }

        if ($request->get("county_id")) {
            $list->where(['county_id' => $request->get("county_id")]);
        }

        if ($request->get("status")) {
            if (is_array($request->get("status"))) {
                $list->whereIn('status', $request->get("status"));
            } else {
                $list->whereIn('status', explode(',', $request->get("status")));
            }
        }

        if ($request->get("medical_unit_name")) {
            $list->where('medical_unit_name', 'like', "%" . $request->get("medical_unit_name") . "%");
        }

        if ($request->get("name")) {
            $list->where('name', 'like', "%" . $request->get("name") . "%");
        }

        if ($request->get("phone_number")) {
            $list->where(['phone_number' => $request->get("phone_number")]);
        }

        $list = $list->with('assigned_user')->paginate($this->per_page);
        return response()->json([
            "data" => [
                'items' => new HelpRequestCollection($list->items()),
                'current_page' => $list->currentPage(),
                'last_page' => $list->lastPage(),
                'per_page' => $list->perPage(),
                'total' => $list->total(),
            ],
            "message" => __("Got collection"),
            "success" => true
        ]);

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return array
     */
    public function store(Request $request)
    {
        $data = $request->post();

        //create new request
        $hr = new HelpRequest;

        $hr->name = $data['name'];
        $hr->job_title = $data['job_title'];
        $hr->phone_number = $data['phone_number'];
        $hr->medical_unit_type_id = $data['medical_unit_type_id'];
        $hr->medical_unit_name = $data['medical_unit_name'];
        $hr->needs_text = $data['needs_text'];
        $hr->extra_info = $data['extra_info'];
        $hr->user_id = $request->user('api') ? $request->user('api')->id : null;

        $hr->saveWithChanges(['change_type_id' => 1, 'changes' => $hr->toArray()]);

        // return the new request so that the angular app can reload
        return [
            'success' => true,
            'newHelpRequest' => new HelpRequestResource(HelpRequest::with(['changes', 'changes.needs', 'assigned_user'])->find($hr->id))
        ];

    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id)
    {
        return new HelpRequestResource(HelpRequest::with(['changes', 'changes.needs', 'assigned_user'])->find($id));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $action = $request->get('action');

        $hr = HelpRequest::find($id);
        switch ($action) {
            case 'changeStatus':
                $hr->status = $request->post('status');
                break;
            case 'assignCurrentUser':
                $hr->assigned_user_id = $request->user('api')->id;
                $return = ['assigned_user' => new UserResource(Auth::user())];
                break;
            case 'unassignCurrentUser':
                $hr->assigned_user_id = null;
                $return = ['assigned_user' => null];
                break;
        }

        $hr->save();
        $return['success'] = true;
        return $return;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
