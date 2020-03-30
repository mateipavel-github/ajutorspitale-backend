<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class HelpRequestChangeNeed extends Model
{
    //
    public function needType() {
        return $this -> hasOne('App\MetadataNeedType');
    }
}
