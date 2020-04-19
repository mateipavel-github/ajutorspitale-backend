<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MetadataDeliveryPlanStatusType extends Model
{
    use SoftDeletes;
    protected $table = "metadata_delivery_plan_status_types";

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'label',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
    ];
}
