<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

use Illuminate\Database\Eloquent\SoftDeletes;

class HelpRequest extends Model
{

    use SoftDeletes;

    protected $fillable = ['assigned_user_id'];

    //
    public function changes()
    {
        return $this->hasMany('App\HelpRequestChange');
    }

    public function assigned_user() {
        return $this -> belongsTo('App\User', 'assigned_user_id', 'id');
    }

    public function medical_unit_type() {
        return $this->hasOne('App\MetadataMedicalUnitType');
    }

    public function saveWithChanges($changeData, $needs=[]) {

        $changes = isset($changeData['changes']) ? $changeData['changes'] : $this -> getChanges();

        if(!empty($changes)) {

            $result = $this->save();

            $rc = new HelpRequestChange;
            $rc->help_request_id = $this->id;
            $rc->change_type_id = $changeData['change_type_id'];
            $rc->user_comment = isset($changeData['user_comment']) ? $changeData['user_comment'] : null;
            $rc->change_log = $changes;
            $rc->user_id = isset($this->user_id) ? $this->user_id : null;

            $rc->save();

            if (isset($needs) && !empty($needs)) {
                foreach ($needs as $need) {
                    $rc->needs()->create([
                        'need_type_id' => $need['need_type_id'],
                        'quantity' => $need['quantity']
                    ])->save();
                }
            }

            return $result;
        }
    }
}
