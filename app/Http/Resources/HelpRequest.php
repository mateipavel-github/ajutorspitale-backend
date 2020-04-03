<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\HelpRequestChange as HelpRequestChangeResource;
use App\Http\Resources\User as UserResource;

class HelpRequest extends JsonResource
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
            'name'=> $this -> name,
            'phone_number' => $this -> phone_number,
            'job_title' => $this -> job_title,
            'medical_unit_type_id' => $this -> medical_unit_type_id,
            'medical_unit_name' => $this -> medical_unit_name,
            'current_needs' => $this -> current_needs,
            'county_id' => $this -> county_id,
            'changes' => HelpRequestChangeResource::collection($this -> whenLoaded('changes')),
            'extra_info' => $this -> extra_info,
            'needs_text' => $this -> needs_text,
            'assigned_user' => new UserResource($this -> whenLoaded('assigned_user')),
            'status' => $this -> status
        ];

    }
}
