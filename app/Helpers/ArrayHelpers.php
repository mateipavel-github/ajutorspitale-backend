<?php

namespace App\Helpers;

class ArrayHelper {

    function associateBy($array, $field) {

        $return = [];
        foreach($array as $item) {
            $return[$item[$field]] = $item;
        }
        return $return;

    }

}

?>