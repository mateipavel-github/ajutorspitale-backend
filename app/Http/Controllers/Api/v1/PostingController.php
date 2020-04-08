<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;

use App\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\HelpRequest;
use App\Http\Resources\HelpRequestCollection;
use \App\Http\Resources\HelpRequest as HelpRequestResource;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\User as UserResource;
use Illuminate\Support\Facades\Log;
use App\MetadataRequestStatusType;

class PostingController extends Controller
{
    protected $postingType = "";
    protected $per_page = 20;

    public function setPostingType($type) {
        $postingType = $type;
        switch($type) {
            case 'request':
                $this->model = 'App\HelpRequest';
                break;
            case 'offer':
                $this->model = 'App\HelpOffer';
                break;
        }
    }

    public function getStatusSelectionIds($statusSelection) {
        if (!is_array($statusSelection)) {
            $statusSelection = explode(',', $statusSelection);
        }

        $statusSelectionIds = [];
        if(is_int($statusSelection[0])) {
            $statusSelectionIds = $statusSelection;
        } else {
            $possibleStatuses = MetadataRequestStatusType::all();
            foreach($possibleStatuses as $ps) {
                if(in_array($ps->slug, $statusSelection)) {
                    $statusSelectionIds[] = $ps['id'];
                }
            }
        }
        
        return $statusSelectionIds;
    }

    public function massAssignToCurrentUser(Request $request)
    {
        $howMany = $request->post('howMany');
        $userId = $request->user('api')->id;

        $requestIds = $this->model::whereIn('status', $this->getStatusSelectionIds('new,approved'))->whereNull('assigned_user_id')->pluck('id')->take($howMany);
        $this->model::whereIn('id', $requestIds)->update(array('assigned_user_id' => $userId));

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

        $list = $this->model::select("*");
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

        if ($statusSelection = $request->get("status")) {
            $statusSelectionIds = $this->getStatusSelectionIds($statusSelection);

            $list->whereIn('status', $statusSelectionIds);
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

        //create new request | offer
        $posting = new $this->model;

        $posting->name = $data['name'];
        $posting->job_title = $data['job_title'];
        $posting->phone_number = $data['phone_number'];
        $posting->medical_unit_name = $data['medical_unit_name'];
        $posting->medical_unit_type_id = $data['medical_unit_type_id'];
        $posting->extra_info = $data['extra_info'];
        $posting->needs_text = $data['needs_text'];
    
        switch( $this->postingType ) {
            case 'request':
                $posting->county_id = $data['county_id'];
                break;
            case 'offer':
                $posting->county_ids = $data['county_ids'];
                break;
        }

        $posting->user_id = $request->user('api') ? $request->user('api')->id : null;

        //new posting status
        $posting->saveWithChanges(['change_type_id' => 1, 'changes' => $posting->toArray()]);

        // return the new request so that the angular app can reload
        return [
            'success' => true,
            'newHelpRequest' => new HelpRequestResource($this->model::with(['changes', 'changes.needs', 'assigned_user', 'notes', 'notes.user'])->find($hr->id))
        ];

    }

    /**
     * Display the specified resource.
     *
     * @param Request $request
     * @param int $id
     * @return HelpRequestResource
     */
    public function show(Request $request, $id)
    {
        return new HelpRequestResource($this->model::with(['changes', 'changes.needs', 'assigned_user', 'medical_unit'])->find($id));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param int $id
     * @return array
     */
    public function update(Request $request, $id)
    {
        $action = $request->get('action');

        $hr = $this->model::find($id);
        switch ($action) {
            case 'changeStatus':
                $hr->status = $request->post('status');
                // if (Auth::user()->isAdmin() || (int)$hr->assigned_user_id === (int)Auth::user()->id) {
                //     $hr->status = $request->post('status');
                // } else {
                //     return ['success'=>false, 'error'=>'Cererea a fost preluată de alt voluntar. Doar el sau un administrator pot schimba statusul.'];
                // }
                break;
            case 'assignCurrentUser':
                $hr->assigned_user_id = $request->user('api')->id;
                $return = ['assigned_user' => new UserResource(Auth::user())];
                break;
            case 'unassignCurrentUser':
                $hr->assigned_user_id = null;
                $return = ['assigned_user' => null];

                // if (Auth::user()->isAdmin() || (int)$hr->assigned_user_id === (int)Auth::user()->id) {
                //     $hr->assigned_user_id = null;
                //     $return = ['assigned_user' => null];
                // } else {
                //     return ['success'=>false, 'error'=>'Doar voluntarul care a preluat cererea sau un administrator pot face această modificare.'];
                // }
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
     * @return Response
     */
    public function destroy($id)
    {
        //
    }
}
