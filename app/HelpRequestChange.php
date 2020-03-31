<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class HelpRequestChange extends Model
{

    protected $casts = [
        'change_log' => 'array',
    ];

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

    public function delivery() {
        return $this->belongsTo('App\Delivery');
    }
}
