<?php

namespace App\Utils;

class CleanQuestionUtils {
    /**
     * Limpia el texto de la pregunta eliminando partes innecesarias
     * @param mixed $text
     * @return string
     */
    public static function clean($text) {
        $patterQuestion = preg_replace('/\n+/', '', $text);
        $patterQuestion = preg_replace(RegexUtils::CLEAN_ALTERNATIVES, '', $text);
        $patterQuestion = preg_replace(RegexUtils::CLEAN_ESSAY, '', $patterQuestion);
        $patterQuestion = preg_replace(RegexUtils::CLEAN_TRUE_FALSE, '', $patterQuestion);
        $patterQuestion = preg_replace('/\s+/', ' ', $patterQuestion);
        return trim($patterQuestion);
    }
}