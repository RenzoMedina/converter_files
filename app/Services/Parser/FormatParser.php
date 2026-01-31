<?php

namespace App\Services\Parser;

use App\Utils\RegexUtils;

class FormatParser{
    public function format($text){
        $old = preg_replace('/.*?plazos establecidos\.\s*/s', '', $text);
        $new = RegexUtils::QUESTION;

        if ($old !== $text) {
            return 'old';
        }
        if (preg_replace($new, "",$text)) {
            return 'new';
        }
    }
}