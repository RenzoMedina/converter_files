<?php

namespace App\Services\Parser;

use App\Utils\ConvertUtils;
use Smalot\PdfParser\Parser;

class LogicParser{
        private $parser;
        public function __construct()
        {
            $this->parser = new Parser();
        }
        public static function transforOld($path){
        $pdf = self::$parser->parseFile($path);
        $text = $pdf->getText();
        $text = preg_replace("/\r\n|\r/", "\n", $text);
        $text = preg_replace('/.*?plazos establecidos\.\s*/s', '', $text);
        $text = preg_replace('/^(?:[A-ZÁÉÍÓÚÜÑ0-9\s\.\-]+?\n){1,3}/', '', $text);

        $pattern = '/^\s*(\d+)\.\s*'
            . '(.*?) '
            . '\s+'
            . 'a\)\s*(.*?)\n'
            . 'b\)\s*(.*?)\n'
            . 'c\)\s*(.*?)\n'
            . 'd\)\s*(.*?)\n'
            . 'e\)\s*(.*?)\n'
            . '.*?Respuesta\s*correcta\s*'
            . 'Retroalimentación\s*'
            . '([aA-eE])'
            . '\s*'
            . '((?:(?!\n\s*\d+\.\s).)*)'
            . '/smx';

        preg_match_all($pattern, $text, $matches, PREG_SET_ORDER);

        $preguntas = [];

        foreach ($matches as $m) {
            $preguntas[] = [
                'numero' => $m[1],
                'pregunta' => trim($m[2]),
                'opciones' => [
                    'a' => trim($m[3]),
                    'b' => trim($m[4]),
                    'c' => trim($m[5]),
                    'd' => trim($m[6]),
                    'e' => trim($m[7]),
                ],
                'respuesta' => strtolower(trim($m[8])),
                'retroalimentacion' => trim($m[9]),
            ];
        }
        if (empty($preguntas)) {
            return 0;
        }
        return (new ConvertUtils())->multiChoicesOld($preguntas);
    }
}