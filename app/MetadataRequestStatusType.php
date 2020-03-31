<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MetadataRequestStatusType extends Model
{
    protected $table = "metadata_request_status_types";

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
