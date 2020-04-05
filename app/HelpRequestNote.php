<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\HelpRequest;
use App\User;

class HelpRequestNote extends Model
{

    protected $table = "help_request_notes";
    protected $with = ['user'];


    public function user() {
        return $this -> belongsTo('App\User', 'user_id', 'id');
    }

    public function request() {
        return $this -> belongsTo('App\HelpRequest', 'help_request_id', 'id');
    }
    
}
