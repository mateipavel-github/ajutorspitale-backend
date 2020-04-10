<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\User as UserResource;

class PostingChange extends JsonResource
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
            'needs' => PostingChangeNeed::collection($this -> whenLoaded('needs')),
            'changes' => $this -> changes,
            'change_type_id' => $this -> change_type_id,
            'status' => $this -> status,
            'user_id' => $this -> user_id,
            'user' => new UserResource($this->whenLoaded('user')),
            'created_at' => $this -> created_at
        ];

    }
}
