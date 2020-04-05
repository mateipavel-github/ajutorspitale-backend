<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

use Illuminate\Database\Eloquent\SoftDeletes;
use App\HelpRequestChange;
use App\HelpRequestChangeNeed;
use App\RequestNote;

class HelpRequest extends Model
{

    use SoftDeletes;

    protected $with = ['changes.user','notes','notes.user'];
    protected $fillable = ['assigned_user_id'];
    protected $casts = [
        'current_needs' => 'array'
    ];

    public function getCurrentNeedsAttribute($value)
    {
        $tables = [
            'hr' => (new HelpRequest()) -> getTable(),
            'hrc' => (new HelpRequestChange()) -> getTable(),
            'hrcn' => (new HelpRequestChangeNeed()) -> getTable(),
        ];

        $request_id = $this->id;

        $list = DB::table($tables['hrcn'])
            ->select('need_type_id', DB::raw('SUM(quantity) as quantity'))
            ->whereIn('help_request_change_id', function ($query) use ($tables, $request_id) {
                $query->select('id')
                    ->from($tables['hrc'])
                    ->where('help_request_id', '=', $request_id);
            })
            ->groupBy('need_type_id')->get();
        return $list;
    }

    //
    public function changes()
    {
        return $this->hasMany('App\HelpRequestChange');
    }

    public function notes() {
        return $this->hasMany('App\HelpRequestNote');
    }


    public function assigned_user() {
        return $this -> belongsTo('App\User', 'assigned_user_id', 'id');
    }

    public function medical_unit_type() {
        return $this->hasOne('App\MetadataMedicalUnitType');
    }

    public function medical_unit() {
        return $this->belongsTo('App\MedicalUnit', 'medical_unit_id', 'id');
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
