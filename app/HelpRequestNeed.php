<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class HelpRequestNeed extends Model
{
    //
    public function need() {
        return $this -> belongsTo('App\MetadataNeed');
    }
}
