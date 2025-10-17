<?php 

namespace App\Services;

use App\Utils\ConvertUtils;
use DOMDocument;

class ConvertService{

    public function transforNew($path){
            $parser = new \Smalot\PdfParser\Parser();
            $pdf = $parser->parseFile($path);
            $text = $pdf->getText();

            $pattern = '/N° de pregunta:\s*(\d+)\s*(.*?)\nAlternativas\s*'
                    . 'a\)\s*(.*?)\n'
                    . 'b\)\s*(.*?)\n'
                    . 'c\)\s*(.*?)\n'
                    . 'd\)\s*(.*?)\n'
                    . 'e\)\s*(.*?)\n'
                    .'.*?Respuesta correcta\s*([aA-eE])\s*'
                    .'Retroalimentación:\s*(.*?)(?=[0-9]|$)/s';
            preg_match_all($pattern, $text, $match, PREG_SET_ORDER);

            $preguntas =[];
            foreach($match as $m){
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
                    'retroalimentacion' => trim($m[9])
                ];
            }
                
         return (new ConvertUtils())->multiChoices($preguntas);       
    }

    public function transforOld($path){
        $parser = new \Smalot\PdfParser\Parser();
        $pdf = $parser->parseFile($path);
        $text = $pdf->getText();
        $text = preg_replace("/\r\n|\r/", "\n", $text);
        $text = preg_replace('/.*?plazos establecidos\.\s*/s','',$text);
        $text = preg_replace('/^(?:[A-ZÁÉÍÓÚÜÑ0-9\s\.\-]+?\n){1,3}/', '', $text);
        
        $pattern = '/^\s*(\d+)\.\s*'
                    .'(.*?) '                                     
                    .'\s+'                                      
                    . 'a\)\s*(.*?)\n'
                    . 'b\)\s*(.*?)\n'
                    . 'c\)\s*(.*?)\n'
                    . 'd\)\s*(.*?)\n'
                    . 'e\)\s*(.*?)\n'                         
                    .'.*?Respuesta\s*correcta\s*'                
                    .'Retroalimentación\s*'                     
                    .'([aA-eE])'
                    .'\s*'                               
                    .'((?:(?!\n\s*\d+\.\s).)*)'                      
                    .'/smx';

            preg_match_all($pattern, $text, $matches, PREG_SET_ORDER);

            $preguntas =[];

            foreach ($matches as $m) {
                $preguntas[] = [
                    'numero'=>$m[1],
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
       return (new ConvertUtils())->multiChoices($preguntas);
    }

}