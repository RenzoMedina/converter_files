<?php

namespace App\Services;

use App\Utils\RegexUtils;

class CleanServices{
    /**
     * Limpia la respuesta correcta
     * @param mixed $answer
     * @return string
     */
    public static function answer($answer) {
        $answer = trim($answer);

        if (preg_match('/^[A-Ea-e]$/', $answer)) {
            return strtolower($answer);
        } elseif (preg_match('/^(verdadero|falso)$/i', $answer)) {
            return strtolower($answer);
        } else {
            return '';
        }
    }
    /**
     * Limpia la retroalimentación eliminando partes innecesarias
     * @param mixed $texto
     * @return string
     */
    public static function feedback($text) {
        if (empty($text))
            return '';

        $retroalimentacionLimpia = trim($text);

        // Limpiar hasta "semana."
        if (preg_match('/(.*?semana\.)/s', $retroalimentacionLimpia, $retroMatch)) {
            return trim(preg_replace('/\s+/', ' ', $retroMatch[1]));
        }

        // Si no termina en "semana.", dividir por líneas y limpiar
        $lineas = explode("\n", $retroalimentacionLimpia);
        $lineasValidas = [];

        foreach ($lineas as $linea) {
            $linea = trim($linea);

            if (empty($linea)) {
                continue;
            }

            if (preg_match('/^\d+$/', $linea)) {
                break;
            }

            if (preg_match('/^(Indicador|N°\s*de\s*pregunta)/i', $linea)) {
                break;
            }

            $lineasValidas[] = $linea;
        }

        $retroalimentacionLimpia = implode(' ', $lineasValidas);
        $retroalimentacionLimpia = preg_replace('/\s+/', ' ', $retroalimentacionLimpia);

        return trim($retroalimentacionLimpia);
    }
    /**
     * Limpia el texto completo dividiéndolo en preguntas y limpiando cada bloque
     * @param mixed $content
     * @return string
     */
    public static function fullText($content) {
        $pregunta = preg_split(RegexUtils::CLEAN_FULL_TEXT, $content, -1, PREG_SPLIT_DELIM_CAPTURE);
        $textoLimpio = '';
        for ($i = 1; $i < count($pregunta); $i += 2) {
            if (isset($pregunta[$i]) && isset($pregunta[$i + 1])) {
                $numeroPregunta = $pregunta[$i];
                $contenidoPregunta = $pregunta[$i + 1];
            }

            $contenidoPregunta = self::questionBlock($contenidoPregunta);

            $textoLimpio .= $numeroPregunta . $contenidoPregunta . "\n\n";
        }
        return trim($textoLimpio);
    }    
    /**
     * Limpia un bloque de pregunta específico
     * @param mixed $block
     * @return string
     */
    private static function questionBlock($block) {
        if (preg_match(RegexUtils::CLEAN_QUESTION, $block, $matches)) {
            return trim($matches[1]);
        }
        return trim($block);
    }
    /**
     *  Filtra y devuelve solo las preguntas válidas
     * 
     * @param mixed $preguntas
     * @return array
     */
    public static function filterValidQuestions($preguntas)
    {
        $preguntasValidas = array_filter($preguntas, function ($p) {
            return isset($p['tipo']) &&
                $p['tipo'] !== 'ERROR' &&
                in_array($p['tipo'], ['multichoice', 'essay', 'truefalse']);
        });


        return array_values($preguntasValidas);
    }
}