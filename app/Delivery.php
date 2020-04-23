<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Delivery extends Model
{
    use SoftDeletes;

    protected $with = ['needs'];
    public $fillable = [
        'sender_name', 
        'sender_contact_name', 
        'sender_phone_number', 
        'sender_address', 
        'sender_county_id',
        'sender_city_name',
        'destination_name', 
        'destination_contact_name', 
        'destination_phone_number', 
        'destination_address', 
        'destination_medical_unit_id', 
        'destination_county_id',
        'destination_city_name',
        'packages',
        'size',
        'weight',
        'main_sponsor_id',
        'delivery_sponsor_id'
    ];

    public function syncNeeds($needs, $execute=false) {
        
        $existingNeeds = collect($this->needs)->keyBy('need_type_id');
        $newNeeds = collect($needs)->keyBy('need_type_id');

        $needsToDelete = $existingNeeds->diffKeys($newNeeds);
        $needsToCreateOrUpdate = $newNeeds->diffKeys($needsToDelete);

        if($execute) {
            //delete
            if($needsToDelete->count()>0) {
                $this->needs()->whereIn('need_type_id', $needsToDelete->keys()->all())->delete();
            }

            foreach($needsToCreateOrUpdate as $n) {
                $this->needs()->updateOrCreate(
                    [ 'need_type_id' => $n['need_type_id'] ],
                    [ 'quantity' => $n['quantity'] ]
                );
            }
        } 
        
        return [
            'to_delete' => $needsToDelete,
            'to_create_or_update' => $needsToCreateOrUpdate
        ];

    }

    /* attributes & relationships */

    public function notes() {
        return $this->morphMany('App\Note', 'item');
    }

    public function needs()
    {
        return $this->hasMany('App\DeliveryNeed');
    }

    public function delivery_requests()
    {
        return $this->hasMany('App\DeliveryPlanHelpRequest');
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

}
