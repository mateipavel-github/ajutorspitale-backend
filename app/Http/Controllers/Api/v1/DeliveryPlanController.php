<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\DeliveryPlan;
use App\HelpOffer;
use App\Http\Resources\DeliveryPlan as DeliveryPlanResource;
use DB;

class DeliveryPlanController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
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
        
        $p = new DeliveryPlan();
        $p -> user_id = $request->user('api')->id;
        $p -> save();

        // if the new delivery plan is connected to an offer, let's grab the offer's delivery items and update the plan accordingly
        if($offerId = $request->post('fromOffer')) {
            $offer = HelpOffer::find($offerId);
            if($offer) {
                $p -> details = ['needs'=>$offer->current_needs];
                $p -> offers() -> attach($offerId);
                $p -> save();
            }
        }

        return response()->json([
            'success'=>true,
            'data' => [
                'id' => $p->id
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
        //
        $plan = DeliveryPlan::with('requests.medical_unit','requests.pivot.delivery','offers')->find($id);
        return new DeliveryPlanResource($plan);

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
        $p = DeliveryPlan::find($id);

        $p->title = $request->post('title');

        $details = $request->post('details');
        if(isset($details['needs'])) {
            $details['needs'] = array_map( function($need) {
                if(!isset($need['need_type_id']) && isset($need['need_type'])) {
                    $need['need_type_id'] = $need['need_type']['id'];
                }
                return ['need_type_id' => $need['need_type_id'], 'quantity' => $need['quantity']];
            }, $details['needs']);
        }

        $p->details = $details;

        $requests = $request->post('requests');
        if(!is_null($requests)) {
            $modelsToSync = [];
            foreach($requests as $request) {
                $modelsToSync[$request['id']] = [
                    'position' => $request['position'],
                    'priority_group' => $request['priority_group'],
                    'details' => isset($request['details']) ? $request['details'] : []
                ];
            }
            $p->requests()->sync($modelsToSync);
        }

        $p->save();

        return response()->json([
            'success'=>true,
            'data' => [
                'item' => $this->show($id)
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
}
