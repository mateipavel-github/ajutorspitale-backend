<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MedicalUnit extends Model
{
    //

    public function help_request() {
        return $this -> hasOne('App\HelpRequest', 'medical_unit_id', 'id');
    }
}
