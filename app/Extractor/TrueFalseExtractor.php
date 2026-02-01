<?php 

namespace App\Extractor;

use App\Utils\RegexUtils;

class TrueFalseExtractor {
    /**
     * Extractor de opciones Verdadero/Falso
     * @param mixed $text
     * @param mixed $alternatives
     * @param mixed $type
     * @return array
     */
    public static function optionsTrueFalse($text, $alternatives = [], $type = '') {
        $patternTrueFalse = RegexUtils::EXTRACT_OPTION_TRUE_FALSE;
        preg_match($patternTrueFalse, $text, $matches);
        if (isset($matches[1])) {
            $textAlternatives = trim($matches[1]);

            $patterOptions = RegexUtils::OPTIONS_TRUE_FALSE;
            preg_match_all($patterOptions, $textAlternatives, $optionMatches, PREG_SET_ORDER);
            foreach ($optionMatches as $opt) {
                $word = $opt[1];
                $content = trim($opt[2]);

                if (stripos($content, 'Verdadero o falso') === false) {
                    $alternatives[$word] = $content;
                    $type = 'truefalse';
                }
            }
        }
        return [$alternatives, $type];
    }
}