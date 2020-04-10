<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

use App\RequestNote;
use App\Posting;

class HelpRequest extends Posting
{
    /* 
     * ATTENTION
     * This class extends Posting - check that out too 
     */

    public function __construct() {
        $this->with = array_merge($this->with, []);
        $this->fillable = array_merge($this->fillable, []);
        $this->casts = array_merge($this->casts, []);
        $this->_editableFields = array_merge($this->_editableFields, ['county_id']);
    }

    public function medical_unit_type() {
        return $this->hasOne('App\MetadataMedicalUnitType');
    }

}
