<?php 

namespace App\Extractor;

use App\Utils\RegexUtils;

class EssayExtractor {
     /**
     * Extrae las opciones de una pregunta de ensayo
     * @param mixed $text
     * @param mixed $type
     */
    public static function essay($text, $type = ''){
        $patternEssay = RegexUtils::EXTRACT_ESSAY;
        if (preg_match($patternEssay, $text)) {
            $type = 'essay';
        }
        return $type;
    }
}