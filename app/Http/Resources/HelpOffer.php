<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\PostingChange as PostingChangeResource;
use App\Http\Resources\User as UserResource;

class HelpOffer extends JsonResource
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
            'created_at' => $this -> created_at,
            'job_title' => $this -> job_title,
            'medical_unit_id' => $this -> medical_unit_id,
            'medical_unit' => $this -> medical_unit,
            'medical_unit_name' => $this -> medical_unit_name,
            'current_needs' => $this -> current_needs,
            'changes' => PostingChangeResource::collection($this -> whenLoaded('changes')),
            'extra_info' => $this -> extra_info,
            'needs_text' => $this -> needs_text,
            'other_needs' => $this -> other_needs,
            'assigned_user' => new UserResource($this -> whenLoaded('assigned_user')),
            'notes' => $this->whenLoaded('notes'),
            'status' => $this -> status
        ];

    }
}
