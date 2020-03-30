<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MetadataUserRoleType extends Model
{
    protected $table = "metadata_user_role_types";

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
