<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class HelpRequest extends Model
{
    //
    public function changes()
    {
        return $this->hasMany('App\HelpRequestChange');
    }

    public function assigned_user() {
        return $this -> belongsTo('App\User', 'assigned_user_id', 'id');
    }

    public function medical_unit_type() {
        return $this->hasOne('App\MetadataMedicalUnitType');
    }
}
