<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use AjCastro\EagerLoadPivotRelations\EagerLoadPivotTrait;

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
                        ->withPivot('delivery_id','details','position','priority_group');
    }

    public function owner() {
        return $this -> belongsTo('App\User', 'user_id', 'id');
    }

    public function assigned_user() {
        return $this -> belongsTo('App\User', 'assigned_user_id', 'id');
    }

    public function main_sponsor() {
        return $this -> hasOne('App\Sponsor', 'id', 'main_sponsor_id');
    }

    public function delivery_sponsor() {
        return $this -> hasOne('App\Sponsor', 'id', 'delivery_sponsor_id');
    }

}
