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

    /*
    public function save($change_type_id, $needs=[]) {

        $changes = $this -> getChanges();

        $rc = new HelpRequestChange;
        $rc->change_type_id=$change_type_id;
        $rc->help_request_id = $this->id;
        $rc->changes = $changes;
        $rc->save();

        // add needs
        /*
        if(isset($data['needs']) && !empty($data['needs'])) {
            foreach($data['needs'] as $need) {
                $rc->needs()->create([
                    'need_type_id' => $need['need_type_id'],
                    'quantity' => $need['quantity']
                ])->save();
            }
        }

        parent::save();
    }
    */

}
