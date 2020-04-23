<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class DeliveryPlanRequest extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $data = parent::toArray($request);
        $data['delivery'] = $data['pivot']['delivery'];
        $data['position'] = $data['pivot']['position'];
        $data['priority_group'] = $data['pivot']['priority_group'];
        $data['pivot']['delivery'] = '!!! moved to $item[\'delivery\'] using DeliveryPlanRequest resource !!!';
        $data['pivot']['position'] = '!!! moved to $item[\'delivery\'] using DeliveryPlanRequest resource !!!';
        $data['pivot']['priority_group'] = '!!! moved to $item[\'delivery\'] using DeliveryPlanRequest resource !!!';
        return $data;
    }
}
