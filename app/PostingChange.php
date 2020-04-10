<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\HelpRequest;

class PostingChange extends Model
{

    use SoftDeletes;

    protected $casts = [
        'change_log' => 'array',
    ];

    public function item() {
        return $this->morphTo();
    }

    //
    public function needs()
    {
        return $this->hasMany('App\PostingChangeNeed');
    }

    public function type() {
        return $this -> hasOne('App\MetadataChangeType');
    }

    public function delivery() {
        return $this->belongsTo('App\Delivery');
    }

    public function user() {
        return $this->belongsTo('App\User');
    }
}
