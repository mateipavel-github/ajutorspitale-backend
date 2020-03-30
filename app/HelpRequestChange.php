<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class HelpRequestChange extends Model
{
    //
    public function needs()
    {
        return $this->hasMany('App\HelpRequestChangeNeed');
    }

    public function helpRequest() {
        return $this->belongsTo('App\HelpRequest');
    }

    public function type() {
        return $this -> hasOne('App\MetadataChangeType');
    }

    public function Delivery() {
        return $this->belongsTo('App\Delivery');
    }
}
