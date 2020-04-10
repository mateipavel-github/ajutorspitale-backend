<?php

namespace app\Helpers;
use Illuminate\Support\Facades\Facade;

/* 
 *
 * A Facade for App\Helpers\MetadataHelper
 * How to use: 
 * use Metadata; // namespace not required, since it's assigned in aliases in config/app.php
 * Metadata::method();
 */

class MetadataFacade extends Facade {
   protected static function getFacadeAccessor() { return 'metadata'; }
}