<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DeliveryNeed extends Model
{

    protected $fillable = ['need_type_id','quantity'];
    //
    public function delivery()
    {
        return $this->belongsTo('App\Delivery');
    }

}
