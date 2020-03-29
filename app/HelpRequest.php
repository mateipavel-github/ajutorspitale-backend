<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class HelpRequest extends Model
{
    //
    public function needs()
    {
        return $this->hasMany('App\HelpRequestNeed');
    }
}
