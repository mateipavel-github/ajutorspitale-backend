<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\DeliveryPlan;
use App\DeliveryPlanPosting;
use App\HelpOffer;
use App\HelpRequest;
use App\Delivery;
use App\DeliveryNeed;
use App\Http\Resources\DeliveryPlan as DeliveryPlanResource;
use DB;
use App\Exports\DeliveryExport;
use Illuminate\Support\Collection;

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
    
    
    public function download($id) {

        $delivery_ids = [];
        $plan = DeliveryPlan::with('requests.pivot.delivery')->find($id);
        foreach($plan->requests as $r) {
            if($r->pivot->delivery !== null) {
                array_push($delivery_ids, $r->pivot->delivery->id);
            }
        }
        return (new DeliveryExport($delivery_ids))->download('deliveries.xlsx');

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
        \Log::info('Plan loaded', ['Plan id' => $id]);

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

        \Log::info('Set plan delivery items', ['Plan id' => $id]);

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

        \Log::info('DONE Set plan delivery items', ['Plan id' => $id]);

        $_SESSION['log_sql'] = [
            'queries' => [],
            'time' => 0
        ];

        if(!is_null($request->post('requests'))) {
            
            $existingRequests = $p->requests->keyBy('id');
            \Log::info('Existing requests', $existingRequests->keys()->all());    
            \Log::info('Existing requests: '.json_encode($p->requests));   

            $postedRequests = collect($request->post('requests'))->keyBy('id');
            \Log::info('Posted requests', $postedRequests->keys()->all());

            $requestsToDetach = $existingRequests->diffKeys($postedRequests);
            $deliveriesToDelete = $requestsToDetach->pluck('pivot.delivery_id');

            $requestsToAttach = $postedRequests->diffKeys($existingRequests);

            $requestsToUpdate = $existingRequests->diffKeys($requestsToDetach);
            
            \Log::info('Requests to detach', $requestsToDetach->pluck('id')->all());
            \Log::info('Deliveries to delete', $deliveriesToDelete->all());

            // delete deliveries
            Delivery::whereIn('id', $deliveriesToDelete->all())->delete();

            // detach requests
            $helpRequestItemType = get_class(new HelpRequest);
            DeliveryPlanPosting::where('item_type', $helpRequestItemType)
                                ->where('delivery_plan_id', $p->id)
                                ->whereIn('item_id', $requestsToDetach->pluck('id')->all())
                                ->delete();


            // sync new and existing requests
            $pdoBindings = [];
            $pdoString = '';
            $postedRequests->each( function($r, $rId) use (&$pdoBindings, &$pdoString, $p, $helpRequestItemType) {
                $pdoString .= '(?,?,?,?,?,?,NOW(),NOW()),';
                array_push($pdoBindings, 
                    $p->id, $helpRequestItemType, $rId, 
                    isset($r['delivery']['id']) ? $r['delivery']['id'] : null,
                    $r['position'], 
                    $r['priority_group']);
            });
            $pivotTable = (new DeliveryPlanPosting())->getTable();
            if(count($pdoBindings)) {
                DB::statement('INSERT INTO '.$pivotTable.' 
                    (delivery_plan_id, item_type, item_id, delivery_id, position, priority_group, created_at, updated_at)
                    VALUES '.trim($pdoString, ',').' 
                    ON DUPLICATE KEY UPDATE 
                        delivery_id=VALUES(delivery_id),
                        position=VALUES(position),
                        updated_at=VALUES(updated_at),
                        priority_group=VALUES(priority_group) ', $pdoBindings);
            }
            
            \Log::info('Requests synced. Moving on to updating delivery data');

            $p->load('requests.pivot.delivery');
            
            $deliveryNeedsSyncPlan = ['to_delete'=>[], 'to_create_or_update'=> []];

            foreach($p->requests as $pr) {
                
                \Log::info('Processing request '. $pr->id);

                $isNewDelivery = false;
                $d = $pr->pivot->delivery;
                if(!$d) {
                    \Log::info('Request '.$pr->id.' does not have an associated delivery');
                    $d = new Delivery();
                    $d -> user_id = request()->user('api')->id;
                    $isNewDelivery = true;
                }

                $deliveryData = $postedRequests->get($pr->id)['delivery'];
                $d->fill(array_merge($deliveryData, $request->post('sender')));

                if(isset($postedRequestDelivery['main_sponsor'])) {
                    $d->main_sponsor_id = $postedRequestDelivery['main_sponsor']['id'];
                } elseif ($plan_main_sponsor) {
                    $d->main_sponsor_id = $plan_main_sponsor['id'];
                }
                
                if(isset($postedRequestDelivery['delivery_sponsor'])) {
                    $d->delivery_sponsor_id = $postedRequestDelivery['delivery_sponsor']['id'];
                } elseif ($plan_delivery_sponsor) {
                    $d->delivery_sponsor_id = $plan_delivery_sponsor['id'];
                }

                $d->save();
                \Log::info('Delivery '. $d->id.', corresponding to request '. $pr->id.' saved with extra data.');
                if($isNewDelivery) {
                    $pr -> pivot -> delivery() -> associate($d);
                    $pr -> pivot -> save();
                    \Log::info('Delivery '. $d->id.' created and associated to pivot');
                }


                // sync delivery needs. "false" flag prevents execution so we can perform it in an optimized manner
                $aux = $d->syncNeeds($deliveryData['needs'], false);
                foreach($aux['to_delete'] as $needTypeId=>$need) {
                    array_push($deliveryNeedsSyncPlan['to_delete'], ['delivery_id'=>$d->id, 'need_type_id'=>$needTypeId]);
                }
                foreach($aux['to_create_or_update'] as $needTypeId=>$need) {
                    array_push($deliveryNeedsSyncPlan['to_create_or_update'], ['delivery_id'=>$d->id, 'need_type_id'=>$needTypeId, 'quantity'=>$need['quantity']]);
                }

                \Log::info('Delivery '. $d->id.' needs syncronized');
            }

            //delete delivery needs that are gone
            if(count($deliveryNeedsSyncPlan['to_delete'])>0) {
                $aux = DeliveryNeed::where(DB::raw('1'),'0');
                foreach($deliveryNeedsSyncPlan['to_delete'] as $td) {
                    $aux->orWhere(function ($query) use ($td) {
                        $query->where('need_type_id', $td['need_type_id'])->where('delivery_id', $td['delivery_id']);
                    });
                }
                $aux->delete();
            }

            //create or update needs
            $pdoBindings = [];
            $pdoString = '';
            foreach($deliveryNeedsSyncPlan['to_create_or_update'] as $tcu) {
                $pdoString .= '(?,?,?, NOW(), NOW()),';
                array_push($pdoBindings, $tcu['delivery_id'], $tcu['need_type_id'], $tcu['quantity']);
            }
            $deliveryNeedsTable = (new DeliveryNeed())->getTable();
            if(count($pdoBindings)) {
                DB::statement('INSERT INTO '.$deliveryNeedsTable.' 
                    (delivery_id, need_type_id, quantity, created_at, updated_at)
                    VALUES '.trim($pdoString, ',').' 
                    ON DUPLICATE KEY UPDATE 
                        quantity=VALUES(quantity),
                        updated_at=VALUES(updated_at)', $pdoBindings);
            }
            
        }
        
        \Log::info('Saving plan');
        $p->save();
        \Log::info('Plan saved');

        $sql_log = $_SESSION['log_sql'];
        unset($_SESSION['log_sql']);

        return response()->json([
            'success'=>true,
            'data' => [
                'item' => $this->show($id),
                'sql_log' => $sql_log
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
