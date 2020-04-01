<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class HelpRequestChangeNeed extends Model
{

    use SoftDeletes;

    protected $fillable = ['need_type_id','quantity'];
    //
    public function needType() {
        return $this -> hasOne('App\MetadataNeedType');
    }
}
