<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\DeliveryPlan;
use App\HelpOffer;
use App\Delivery;
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
        $plan = DeliveryPlan::with('requests.medical_unit','requests.pivot.delivery.medical_unit','offers', 'main_sponsor', 'delivery_sponsor')->find($id);
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
        $p = DeliveryPlan::with('requests.pivot.delivery')->find($id);

        $p->title = $request->post('title');

        $p->sender_name = $request->post('sender')['sender_name'];
        $p->sender_contact_name = $request->post('sender')['sender_contact_name'];
        $p->sender_phone_number = $request->post('sender')['sender_phone_number'];
        $p->sender_address = $request->post('sender')['sender_address'];
        $p->sender_city_name = $request->post('sender')['sender_city_name'];
        $p->sender_county_id = $request->post('sender')['sender_county_id'];

        if($plan_main_sponsor = $request->post('main_sponsor')) {
            $p->main_sponsor_id = $plan_main_sponsor['id'];
        }
        if($plan_delivery_sponsor = $request->post('delivery_sponsor')) {
            $p->delivery_sponsor_id = $plan_delivery_sponsor['id'];
        }

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

            $postedRequestsIds = [];
            foreach($requests as $r) {
                array_push($postedRequestsIds, $r['id']);
            }
            
            // loop through existing plan requests (before sync-ing the new requests) and see which 
            foreach($p->requests as $r) {
                $postedRequestIndex = array_search($r->id, $postedRequestsIds);
                if($postedRequestIndex === false) {
                    // if we need to delete a plan_request, delete deliveries:
                    if($r->pivot->delivery) {
                        $r->pivot->delivery->needs()->delete();
                        $r->pivot->delivery->delete();
                    }
                } else {
                    $postedRequestDelivery = $requests[$postedRequestIndex]['delivery'];
                    $d = $r->pivot->delivery;
                    if(!$d) {
                        $d = new Delivery();
                        $d -> save();
                        $r->pivot->delivery()->associate($d);
                    }
                    
                    // if we need to update a plan_request, update the delivery first
                    $d->fill(array_merge($postedRequestDelivery, $request->post('sender')));

                    if(isset($postedRequestDelivery['main_sponsor'])) {
                        $d->main_sponsor_id = $postedRequestDelivery['main_sponsor']['id'];
                    } elseif ($plan_main_sponsor) {
                        $d->main_sponsor_id = $plan_main_sponsor['id'];
                    }
                    
                    if(isset($postedRequestDelivery['delivery_sponsor'])) {
                        $d->delivery_sponsor_id = $postedRequestDelivery['delivery_sponsor']['id'];
                    } elseif ($plan_delivery_sponsor) {
                        $d->delivery_sponsor_id = $plan_delivery_sponsor;
                    }

                    $d->destination_medical_unit_id = isset($postedRequestDelivery['medical_unit']) ? $postedRequestDelivery['medical_unit']['id'] : null;
                    $d->save();
                    // sync delivery needs 
                    $d->syncNeeds($postedRequestDelivery['needs']);
                    $requests[$postedRequestIndex]['delivery_id'] = $d->id;
                }

            }
            $modelsToSync = [];
            foreach($requests as $request) {
                $modelsToSync[$request['id']] = [
                    'delivery_id' => isset($request['delivery_id']) ? $request['delivery_id'] : 0,
                    'position' => $request['position'],
                    'priority_group' => $request['priority_group'],
                    'details' => isset($request['details']) ? $request['details'] : []
                ];
            }
            $syncResults = $p->requests()->sync($modelsToSync);

            //create delivery and delivery_needs for newly attached requests
            $requests_added = $p->requests()->whereIn('id', $syncResults['attached'])->get();

            foreach($requests_added as $r) {
                $postedRequestIndex = array_search($r->id, $postedRequestsIds);
                $postedRequestDelivery = $requests[$postedRequestIndex]['delivery'];
                $r->pivot->delivery;
                if(!$d) {
                    $d = new Delivery();
                    $d -> save();
                    $r->pivot->delivery()->associate($d);
                }
                $d->fill(array_merge($postedRequestDelivery, $request->post('sender')));
                if(isset($postedRequestDelivery['main_sponsor'])) {
                    $d->main_sponsor_id = $postedRequestDelivery['main_sponsor']['id'];
                } elseif ($plan_main_sponsor) {
                    $d->main_sponsor_id = $plan_main_sponsor['id'];
                }   
                if(isset($postedRequestDelivery['delivery_sponsor'])) {
                    $d->delivery_sponsor_id = $postedRequestDelivery['delivery_sponsor']['id'];
                } elseif ($plan_delivery_sponsor) {
                    $d->delivery_sponsor_id = $plan_delivery_sponsor;
                }
                $d->save();
                // sync delivery needs 
                $d->syncNeeds($postedRequestDelivery['needs']);
            }

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
