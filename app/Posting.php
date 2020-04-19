<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use DB;

class Posting extends Model
{
    use SoftDeletes;
    //
    protected $with = ['notes','notes.user','changes.user'];
    protected $fillable = ['assigned_user_id'];
    protected $casts = ['current_needs'=>'array'];

    protected $_editableFields = [
        'status', 'medical_unit_id',
        'medical_unit_name', 'name', 'phone_number', 'job_title',
        'needs_text', 'extra_info', 'other_needs'
    ];

    public function getEditableFields($scope='all') {
        return $this->_editableFields;
    }

    /* relationships */

    public function medical_unit() {
        return $this->belongsTo('App\MedicalUnit', 'medical_unit_id', 'id');
    }

    public function notes() {
        return $this->morphMany('App\Note', 'item');
    }

    public function changes() {
        return $this->morphMany('App\PostingChange', 'item');
    }

    public function assigned_user() {
        return $this -> belongsTo('App\User', 'assigned_user_id', 'id');
    }

    public function delivery_plans()
    {
        return $this->morphToMany('App\DeliveryPlan', 'item', 'delivery_plan_posting')
                        ->using(str_replace('App\\', 'App\\DeliveryPlan', get_class($this)))
                        ->withTimestamps()
                        ->withPivot('details','delivery_id');
    }

    /* aggregate needs */

    public function _getCurrentNeedsAttribute() {
        $tables = [
            'p' => $this->getTable(),
            'pc' => (new PostingChange()) -> getTable(),
            'pcn' => (new PostingChangeNeed()) -> getTable(),
        ];

        $posting_id = $this->id;

        $list = DB::table($tables['pcn'])
            ->select('need_type_id', DB::raw('SUM(quantity) as quantity'))
            ->whereIn('posting_change_id', function ($query) use ($tables, $posting_id) {
                $query->select('id')
                    ->from($tables['pc'])
                    ->where('item_id', '=', $posting_id)
                    ->where('item_type', '=', get_class($this));
            })
            ->groupBy('need_type_id')->get();

        return $list;
    }

    //$value can be anything, we won't use it.
    //current_needs is an aggregate of help_request_change_needs for the help_request_changes of this request
    public function setCurrentNeedsAttribute($value) {
        $this -> attributes['current_needs'] = $this->castAttributeAsJson('current_needs', $this->_getCurrentNeedsAttribute());
    }

    public function getCurrentNeedsAttribute()
    {
        return $this->_getCurrentNeedsAttribute();
    }

    public function createWithChanges($changeTypeId, $needs=[]) {

        $changes = $this->getDirty();
        $saved = $this->save();
         
        if(!empty($needs)) {
            $changes['needs'] = true;   
        }

        if($saved) {
            $pc = new PostingChange;

            $pc->change_type_id = $changeTypeId;
            $pc->user_comment = isset($changeData['user_comment']) ? $changeData['user_comment'] : null;
            $pc->user_id = isset($this->user_id) ? $this->user_id : null;

            $this->changes()->save($pc);

            if (isset($needs) && !empty($needs)) {
                $changes['needs'] = true;
                $pc->needs()->createMany($needs);
            }

            $pc->change_log = $changes;
            $pc->save();
        }

        return [
            'success' => $saved, 
            'data'=> [ 'new_item' => $this->fresh() ]
        ];

    }

    public function getPostingType($for='sql') {
        switch($for) {
            case 'sql':
                return addslashes(get_class($this));
                break;
            default:
                return get_class($this);
                break;
        }
    }
}
