<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MetadataNeedType extends Model
{
    use SoftDeletes;
    protected $fillable = ['label', 'slug'];
}
