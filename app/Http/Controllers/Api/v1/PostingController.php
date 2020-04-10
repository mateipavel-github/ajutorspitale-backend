<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;

//models
use App\User;
use App\HelpRequest;
use App\Note;
use App\PostingChange;

//resoureces
use \App\Http\Resources\HelpRequest as HelpRequestResource;
use App\Http\Resources\User as UserResource;
use App\Http\Resources\HelpRequestCollection;

//helpers
use App\Helpers\ArrayHelper;
use Metadata; //there's an alias for this in config/app.php

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;



class PostingController extends Controller
{
    protected $postingType = "";
    protected $per_page = 20;

    public function setPostingType($type) {
        $this->postingType = $type;
        switch($type) {
            case 'request':
                $this->model = 'App\HelpRequest';
                $this->resource = 'App\Http\Resources\HelpRequest';
                break;
            case 'offer':
                $this->model = 'App\HelpOffer';
                $this->resource = 'App\Http\Resources\HelpOffer';
                break;
        }
    }

    private function _getStatusSelectionIds($statusSelection) {
        if (!is_array($statusSelection)) {
            $statusSelection = explode(',', $statusSelection);
        }
        if(is_int($statusSelection[0])) {
            return $statusSelection;
         } else { 
            return $this->type==='request' ? Metadata::getRequestStatusIdsFromSlugs($statusSelection) : Metadata::getOfferStatusIdsFromSlugs($statusSelection);
         }
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
            $list->whereIn('status', $this->_getStatusSelectionIds($statusSelection));
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

        $requestDataFields = $posting->getEditableFields();
        
        foreach($requestDataFields as $field) {
            if(isset($data[$field])) {
                $posting->{$field} = $data[$field];
            }
        }

        $posting->user_id = $request->user('api') ? $request->user('api')->id : null;
        $posting->status = Metadata::getRequestStatusIdFromSlug('new');
        
        $posting->createWithChanges(
            Metadata::getChangeTypeIdFromSlug('new_request'),
            isset($data['needs']) ? $data['needs'] : []
        );

        // return the new request so that the angular app can reload
        return [
            'success' => true,
            'new_item' => new $this->resource($this->model::with(['changes', 'changes.needs', 'assigned_user', 'notes', 'notes.user'])->find($posting->id))
        ];

    }

    /**
     * Display the specified resource.
     *
     * @param Request $request
     * @param int $id
     * @return HelpRequestResource | HelpOfferResource
     */
    public function show(Request $request, $id)
    {
        return new $this->resource($this->model::with(['changes', 'changes.needs', 'assigned_user', 'medical_unit'])->find($id));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param int $id
     * @return array
     */

    public function update(Request $request, $id) {
        
        $return = [
            'success' => false,
            'data' => []
        ];

        $data = $request->post();

        $posting = $posting = $this->model::find($id);
        
        $requestDataFields = $posting->getEditableFields();
        
        foreach($requestDataFields as $field) {
            if(isset($data[$field])) {
                $posting->{$field} = $data[$field];
            }
        }

        // @frontTodo move all updates to /update route
        $action = $request->get('action');
        if($action) {
            $data['change_data'] = ['change_type_id' => Metadata::getChangeTypeIdFromSlug('error')];
            switch ($action) {
                case 'changeStatus':
                    // already processed above ($posting->status = $request->post('status'))
                    break;
                case 'assignCurrentUser':
                    $posting->assigned_user_id = $request->user('api')->id;
                    $return['data']['assigned_user'] = new UserResource(Auth::user());
                    break;
                case 'unassignCurrentUser':
                    $posting->assigned_user_id = null;
                    $return['data']['assigned_user'] = null;
                    break;
            }
        }
        
        $changes = [];

        if($posting->isDirty()) {
            //see what's modified so we can store it for auditing purposes in the change_log field of posting_changes
            $changes = $posting->getDirty();
            //make sure we can save before adding the change
            $postingSaved = $posting->save();

            if($postingSaved) {
                $return['success'] = true;
            } 
        }

        if(isset($data['needs']) && !empty($data['needs'])) {
            $changes['needs'] = true;
            $needsToAdd = $data['needs'];
        }
        
        // @frontTodo set change_data separately into a FormGroup
        //create new change
        $pc = new PostingChange;
        $pc->user_id = $request->user('api') ? $request->user('api')->id : null;
        $pc->change_type_id = $data['change_data']['change_type_id'];
        $pc->user_comment = isset($data['change_data']['user_comment']) ? $data['change_data']['user_comment'] : null;
        $posting->changes()->save($pc);

        //add needs
        if(isset($needsToAdd)) {
            $pc->needs()->createMany($needsToAdd);
            $return['success'] = true;
        }

        //save request change again, to trigger observer so that the current_needs of Posting get updated.
        $pc->change_log = $changes;
        $pc->save();

        // return the new request so that the angular app can reload
        return !empty($return['data']) ? $return : [
            'success' => $return['success'],
            'data' => [
                'item' => new $this->resource($this->model::with(['changes','changes.needs','assigned_user'])->find($posting->id))
            ]
        ];
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

    public function addNote(Request $request, $postingId) {
        
        $posting = $this->model::find($postingId);
        
        $note = new Note(['content' => $request->post('content')]);
        $note->user()->associate($request->user('api'));
        $posting->notes()->save($note);

        return [
            'success' => true,
            'data' => [
                'new_note' => $note
            ]
        ];
    }
}
