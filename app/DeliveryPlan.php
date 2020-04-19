<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DeliveryPlan extends Model
{
    use SoftDeletes;
    //
    protected $with = ['offers','requests','owner'];
    protected $fillable = ['assigned_user_id'];
    protected $casts = ['details'=>'array'];

    /* relationships */

    public function offers() {
        return $this->morphedByMany('App\HelpOffer', 'item', 'delivery_plan_posting')
                        ->using('App\DeliveryPlanHelpOffer')
                        ->withTimestamps()
                        ->withPivot('details','delivery_id');
    }

    public function requests() {
        return $this->morphedByMany('App\HelpRequest', 'item', 'delivery_plan_posting')
                        ->using('App\DeliveryPlanHelpRequest')
                        ->withTimestamps()
                        ->withPivot('details','delivery_id','position','priority_group');
    }

    public function owner() {
        return $this -> belongsTo('App\User', 'user_id', 'id');
    }

    public function assigned_user() {
        return $this -> belongsTo('App\User', 'assigned_user_id', 'id');
    }

}
