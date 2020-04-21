<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;

//models
use App\User;
use App\HelpRequest;
use App\Note;
use App\PostingChange;

//resoureces
use App\Http\Resources\User as UserResource;
use App\Http\Resources\HelpRequestCollection;
use App\Http\Resources\HelpOfferCollection;

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
                $this->resourceCollection = 'App\Http\Resources\HelpRequestCollection';
                $this->newPostingStatusId = Metadata::getRequestStatusIdFromSlug('new');
                break;
            case 'offer':
                $this->model = 'App\HelpOffer';
                $this->resource = 'App\Http\Resources\HelpOffer';
                $this->resourceCollection = 'App\Http\Resources\HelpOfferCollection';
                $this->newPostingStatusId = Metadata::getOfferStatusIdFromSlug('new');
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
            return $this->postingType==='request' ? Metadata::getRequestStatusIdsFromSlugs($statusSelection) : Metadata::getOfferStatusIdsFromSlugs($statusSelection);
         }
    }

    public function massAssignToCurrentUser(Request $request)
    {
        $howMany = $request->post('howMany');
        $userId = $request->user('api')->id;

        $itemIds = $this->model::whereIn('status', $this->getStatusSelectionIds('new,approved'))->whereNull('assigned_user_id')->pluck('id')->take($howMany);
        $this->model::whereIn('id', $itemIds)->update(array('assigned_user_id' => $userId));

        return ['success' => true];
    }


    public function filterByNeeds(Request $request) {

        $list = $this->model::select("*")->with('changes.needs');

        if ($request->get("per_page")) {
            $this->per_page = $request->get('per_page');
        }

        if($needs = $request->get('needs')) {
            $needs = explode(',', $needs);
            $changeItemType = $this->model;
            $list->whereIn('id', function($query) use($changeItemType, $needs) {

                $needTypes = [];
                foreach($needs as $need) {
                    list($need_type,$quantity) = explode(':', $need);
                    $needTypes[]=$need_type;
                }

                $pcTbl = (new \App\PostingChange)->getTable();
                $pcnTbl = (new \App\PostingChangeNeed)->getTable();

                $query->select('item_id')
                    ->from($pcTbl)
                    ->join($pcnTbl, $pcTbl.'.id', '=', $pcnTbl.'.posting_change_id')
                    ->where($pcTbl.'.item_type', $changeItemType)
                    ->whereIn($pcnTbl.'.need_type_id', $needTypes)
                    ->groupBy($pcTbl.'.item_id', $pcnTbl.'.need_type_id')
                    ->havingRaw('SUM(quantity) > ?', [0]);
            
            });
        }

        if ($mut = $request->get("medical_unit_type_id")) {
            if(!is_array($mut)) { 
                $mut = explode(',', $mut);
            }
            $list->whereIn('medical_unit_type_id', $mut);
        }

        if ($statusSelection = $request->get("status")) {
            $list->whereIn('status', $this->_getStatusSelectionIds($statusSelection));
        }

        if ($keyword = $request->get("keyword")) {
            $list->where(function($q) use ($keyword) {
                if(is_numeric($keyword) && strlen($keyword)<7) {
                    $q->where('id','=',$keyword);
                } else {
                    $q->where('name', 'like', "%" . $keyword . "%");
                    $q->orWhere('phone_number', 'like', "%" . $keyword . "%");
                    $q->orWhere('medical_unit_name', 'like', "%" . $keyword . "%");
                }
            });
        }

        if ($request->get("phone_number")) {
            $list->where(['phone_number' => $request->get("phone_number")]);
        }
        if ($counties = $request->get("county")) {
            if(!is_array($counties)) { 
                $counties = explode(',', $counties);
            }
            switch($this->postingType) {
                case 'request':
                    $list->whereIn('county_id', $counties);
                    break;
                case 'offer':
                    $list->whereIn('id', function($query) use($counties) {
                        $query->select('help_offer_id')
                            ->from(with(new \App\HelpOfferCounty)->getTable())
                            ->whereIn('county_id', $counties);
                    });
                    break;
            }
        }

        $list = $list->with('assigned_user')->paginate($this->per_page);
        return response()->json([
            "data" => [
                'items' => new $this->resourceCollection($list->items()),
                'current_page' => $list->currentPage(),
                'last_page' => $list->lastPage(),
                'per_page' => $list->perPage(),
                'total' => $list->total()
            ],
            "message" => __("Got collection"),
            "success" => true
        ]);
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request)
    {

        if($request->get('needs')) {
            return $this->filterByNeeds($request);
        }

        $list = $this->model::select("*");

        if($this->postingType==='offer') {
            $list->with('counties');
        }

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

        if ($mut = $request->get("medical_unit_type_id")) {
            if(!is_array($mut)) { 
                $mut = explode(',', $mut);
            }
            $list->whereIn('medical_unit_type_id', $mut);
        }

        if ($keyword = $request->get("keyword")) {
            $list->where(function($q) use ($keyword) {
                if(is_numeric($keyword) && strlen($keyword)<7) {
                    $q->where('id','=',$keyword);
                } else {
                    $q->where('name', 'like', "%" . $keyword . "%");
                    $q->orWhere('phone_number', 'like', "%" . $keyword . "%");
                    $q->orWhere('medical_unit_name', 'like', "%" . $keyword . "%");
                }
            });
        }

        if ($counties = $request->get("county")) {
            if(!is_array($counties)) { 
                $counties = explode(',', $counties);
            }
            switch($this->postingType) {
                case 'request':
                    $list->whereIn('county_id', $counties);
                    break;
                case 'offer':
                    $list->whereIn('id', function($query) use($counties) {
                        $query->select('help_offer_id')
                            ->from(with(new \App\HelpOfferCounty)->getTable())
                            ->whereIn('county_id', $counties);
                    });
                    break;
            }
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
                'items' => new $this->resourceCollection($list->items()),
                'current_page' => $list->currentPage(),
                'last_page' => $list->lastPage(),
                'per_page' => $list->perPage(),
                'total' => $list->total()
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

        $posting->status = $this->newPostingStatusId;
        
        $posting->createWithChanges(
            Metadata::getChangeTypeIdFromSlug('new_request'),
            isset($data['needs']) ? $data['needs'] : []
        );

        switch($this->postingType) {
            case 'offer':
                $counties = array_map(function($county_id) {
                    return ['county_id'=>$county_id];
                }, $data['counties_list']);
                $posting->counties()->createMany($counties);
                break;
        }

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

        if($this->postingType === 'offer' && isset($data['counties_list']) && !empty($data['counties_list'])) {
            $counties = array_map(function($county_id) use ($posting) {
                $posting->counties()->updateOrCreate(['county_id'=>$county_id]);
            }, $data['counties_list']);
            $changes['counties'] = true;
            $return['success'] = true;
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
