<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Delivery;
use App\Note;
use Metadata;

class DeliveryController extends Controller
{

    private function _getStatusSelectionIds($statusSelection) {
        if (!is_array($statusSelection)) {
            $statusSelection = explode(',', $statusSelection);
        }
        if(is_int($statusSelection[0])) {
            return $statusSelection;
         } else { 
            return Metadata::getDeliveryStatusIdsFromSlugs($statusSelection);
         }
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {

        // if($request->get('needs')) {
        //     return $this->filterByNeeds($request);
        // }

        $list = Delivery::select("*");

        if ($request->get("per_page")) {
            $this->per_page = $request->get('per_page');
        }

        if ($request->get("user_id")) {
            $list->where(['user_id' => $request->get("user_id")]);
        }

        if ($request->get("medical_unit_id")) {
            $list->where(['destination_medical_unit_id' => $request->get("medical_unit_id")]);
        }

        if ($counties = $request->get("county")) {
            if(!is_array($counties)) { 
                $counties = explode(',', $counties);
            }
            $list->whereIn('county_id', $counties);
        }

        if ($statusSelection = $request->get("status")) {
            $list->whereIn('status', $this->_getStatusSelectionIds($statusSelection));
        }

        if ($keyword = $request->get("keyword")) {
            $list->where(function($q) use ($keyword) {
                if(is_numeric($keyword) && strlen($keyword)<7) {
                    $q->where('id','=',$keyword);
                } else {
                    $q->where('contact_name', 'like', "%" . $keyword . "%");
                    $q->orWhere('description', 'like', "%" . $keyword . "%");
                    $q->orWhere('contact_phone_number', 'like', "%" . $keyword . "%");
                    $q->orWhere('destination_address', 'like', "%" . $keyword . "%");
                }
            });
        }

        if ($request->get("phone_number")) {
            $list->where(['contact_phone_number' => $request->get("phone_number")]);
        }

        $list = $list->with(['owner', 'medical_unit', 'main_sponsor','delivery_sponsor'])->paginate($this->per_page);
        return response()->json([
            "data" => [
                'items' => $list->items(),
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
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
    
        $d = new Delivery;
        $mappings = [
            'name' => 'contact_name',
            'description' => 'description',
            'phone_number' => 'contact_phone_number',
            'address' => 'destination_address',
            'county_id' => 'county_id',
            'medical_unit_id' => 'destination_medical_unit_id'
        ];

        foreach($mappings as $post_key=>$key) {
            $d->$key = $request->post($post_key);
        }

        if($request->post('main_sponsor')) {
            $d->main_sponsor_id = $request->post('main_sponsor')['id'];
        }
        if($request->post('delivery_sponsor')) {
            $d->delivery_sponsor_id = $request->post('delivery_sponsor')['id'];
        } 

        $d->user_id = $request->user('api')->id;

        $d->save();

        if($request->post('requests')) {
            $requests = collect($request->post('requests'))->pluck('id');
            $d->requests()->attach($requests);
        }

        if($request->post('needs')) {
            $needs = $request->post('needs');
            array_map(function($need) use ($d) {
                    $d->needs()->updateOrCreate(['need_type_id'=>$need['need_type_id'], 'quantity'=>$need['quantity']]);
            }, $needs);
        }
        return response()->json([
            'success'=>true,
            'data' => [
                'item' => Delivery::with(['requests', 'notes', 'needs', 'owner', 'main_sponsor','delivery_sponsor','medical_unit'])->find($d->id)
            ]
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return Delivery::with(['requests', 'notes', 'needs', 'owner', 'main_sponsor','delivery_sponsor','medical_unit'])->find($id);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        
        $d = Delivery::find($id);

        $mappings = [
            'name' => 'contact_name',
            'phone_number' => 'contact_phone_number',
            'description' => 'description',
            'address' => 'destination_address',
            'county_id' => 'county_id',
            'medical_unit_id' => 'destination_medical_unit_id',
            'status' => 'status'
        ];

        foreach($mappings as $post_key=>$key) {
            if($request->post($post_key)) {
                $d->$key = $request->post($post_key);
            }
        }

        if($request->post('main_sponsor')) {
            $d->main_sponsor_id = $request->post('main_sponsor')['id'];
        }
        if($request->post('delivery_sponsor')) {
            $d->delivery_sponsor_id = $request->post('delivery_sponsor')['id'];
        } 

        $d->save();

        if($request->post('requests')) {
            $requests = array_unique(collect($request->post('requests'))->pluck('id')->toArray());
            $d->requests()->sync($requests);
        }

        if($request->post('needs')) {
            $existingNeedsByRelationshipId = $d->needs()->pluck('id')->toArray();
            $postedNeedsByRelationshipId = array_unique(collect($request->post('needs'))->pluck('id')->toArray());
            $needsToDelete = array_diff($existingNeedsByRelationshipId, $postedNeedsByRelationshipId);
            $d->needs()->whereIn('id', $needsToDelete)->delete();

            $needs = $request->post('needs');
            array_map(function($need) use ($d) {
                $d->needs()->updateOrCreate(['need_type_id'=>$need['need_type_id'], 'quantity'=>$need['quantity']]);
            }, $needs);
        }

        return response()->json([
            'success'=>true,
            'data' => [
                'item' => Delivery::with(['requests', 'notes', 'needs', 'owner', 'main_sponsor','delivery_sponsor','medical_unit'])->find($d->id)
            ]
        ]);

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function addNote(Request $request, $deliveryId) {
        
        $d = Delivery::find($deliveryId);
        
        $note = new Note(['content' => $request->post('content')]);
        $note->user()->associate($request->user('api'));
        $d->notes()->save($note);

        return [
            'success' => true,
            'data' => [
                'new_note' => $note
            ]
        ];
    }
}
