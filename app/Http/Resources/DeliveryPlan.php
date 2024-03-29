<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\PostingChange as PostingChangeResource;
use App\Http\Resources\User as UserResource;
use App\Http\Resources\DeliveryPlanRequestCollection;
use App\Http\Resources\DeliveryPlanRequest;

class DeliveryPlan extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this -> id,
            'title'=> $this -> title,
            'details' => $this -> details,
            'requests' => new DeliveryPlanRequestCollection($this -> requests),
            'offers' => $this -> offers,
            'assigned_user' => new UserResource($this -> whenLoaded('assigned_user')),
            'owner' => new UserResource($this -> whenLoaded('owner')),
            'main_sponsor' => $this -> whenLoaded('main_sponsor'),
            'delivery_sponsor' => $this -> whenLoaded('delivery_sponsor'),
            'status' => $this -> status,
            'sender' => [
                'sender_name' => $this->sender_name,
                'sender_contact_name' => $this->sender_contact_name,
                'sender_phone_number' => $this->sender_phone_number,
                'sender_address' => $this->sender_address,
                'sender_city_name' => $this->sender_city_name,
                'sender_county_id' => $this->sender_county_id
            ]
        ];

    }
}
