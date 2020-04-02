<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MetadataCounty extends Model
{
    use SoftDeletes;
    protected $table = 'metadata_counties';
}
