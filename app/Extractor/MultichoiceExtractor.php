<?php 

namespace App\Extractor;

use App\Utils\RegexUtils;

class MultichoiceExtractor {
    /**
     * Extrae las opciones de una pregunta de opción múltiple
     * @param mixed $text
     * @param mixed $alternatives
     * @param mixed $type
     * @return array
     */
    public static function optionsMultichoice($text, $alternatives = [], $type = '') {
        $alternativesPattern = RegexUtils::EXTRACT_OPTIONS_MULTICHOICES;
        preg_match($alternativesPattern, $text, $matches);
        if (isset($matches[0])) {
            $textAlternatives = trim($matches[0]);

            $patterOptions = RegexUtils::OPTIONS_MULTICHOICES;
            preg_match_all($patterOptions, $textAlternatives, $optionMatches, PREG_SET_ORDER);
            foreach ($optionMatches as $opt) {
                $word = strtolower($opt[1]);
                $content = trim($opt[2]);

                if (stripos($content, 'Alternativas') === false) {
                    $alternatives[$word] = $content;
                    $type = 'multichoice';
                }
            }
        }
        return [$alternatives, $type];
    }
}