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
        $data['pivot']['delivery'] = '!!! moved from DeliveryPlanRequest resource to $item[\'delivery\'] !!!';
        return $data;
    }
}
