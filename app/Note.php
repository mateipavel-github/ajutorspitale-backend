<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\HelpRequest;
use App\User;

class Note extends Model
{

    protected $table = "notes";
    protected $with = ['user'];
    protected $fillable = ['content','user_id'];

    public function item() {
        return $this->morphTo();
    }

    public function user() {
        return $this -> belongsTo('App\User', 'user_id', 'id');
    }
    
}
