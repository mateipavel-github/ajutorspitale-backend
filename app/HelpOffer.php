<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class HelpOffer extends Posting
{
    public function __construct() {
        $this->with = array_merge($this->with, []);
        $this->fillable = array_merge($this->fillable, []);
        $this->casts = array_merge($this->casts, []);
        $this->_editableFields = array_merge($this->_editableFields, []);
    }

    public function counties() {
        return $this -> hasMany('App\HelpOfferCounty');
    }
}
