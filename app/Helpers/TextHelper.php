<?php

namespace App\Helpers;

class TextHelper {

    public function __construct() {
    }

    public function englishCharactersOnly($string) {
        $toReplace = ['Ă','ă','Â','â','Î','î','Ș','ș','Ț','ț'];
        $replaceWith = ['A','a','A','a','I','i','S','s','T','t'];
        return str_replace($toReplace, $replaceWith, $string);
    }    
}