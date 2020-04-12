<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Delivery extends Model
{

    protected $with = ['needs'];

    public function notes() {
        return $this->morphMany('App\Note', 'item');
    }

    public function needs()
    {
        return $this->hasMany('App\DeliveryNeed');
    }

    public function medical_unit() {
        return $this->belongsTo('App\MedicalUnit', 'destination_medical_unit_id', 'id');
    }

    public function owner() {
        return $this -> belongsTo('App\User', 'user_id', 'id');
    }

    public function main_sponsor() {
        return $this -> hasOne('App\Sponsor', 'id', 'main_sponsor_id');
    }

    public function delivery_sponsor() {
        return $this -> hasOne('App\Sponsor', 'id', 'delivery_sponsor_id');
    }

    public function requests()
    {
        return $this->belongsToMany('App\HelpRequest')->withTimestamps();
    }

    public function offers()
    {
        return $this->belongsToMany('App\HelpOffer')->withTimestamps();
    }
}
