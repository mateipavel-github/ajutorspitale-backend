<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class HelpRequestChange extends Model
{

    use SoftDeletes;

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
