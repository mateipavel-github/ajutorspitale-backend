<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphPivot;

class DeliveryPlanPosting extends MorphPivot
{
    //
    protected $table = 'delivery_plan_posting';
    protected $casts = ['details'=>'array'];

    public function delivery() {
        return $this->belongsTo('App\Delivery');
    }

    public function setDetailsAttribute($value) {
        $this -> attributes['details'] = $this->castAttributeAsJson('details', $value);
    }
}
