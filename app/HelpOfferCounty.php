<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class HelpOfferCounty extends Model
{
    protected $fillable = ['county_id', 'help_offer_id'];
    
    function offer() {
        return $this->belongsTo('App\HelpOffer', 'help_offer_id', 'id');
    }
}
