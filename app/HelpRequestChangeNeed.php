<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class HelpRequestChangeNeed extends Model
{

    protected $fillable = ['need_type_id','quantity'];
    //
    public function needType() {
        return $this -> hasOne('App\MetadataNeedType');
    }
}
