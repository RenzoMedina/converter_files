<?php

namespace App\Build;

use App\Interface\BuildInterface;
use App\Services\FileService;

class GitfBuild implements BuildInterface{
     private string $gift = '';
    public function multiChoicesOld($preguntas, $nameFile = null){
        FileService::cleanOldFiles("./files/archivo_*.txt");

        $this->gift = '';
        foreach($preguntas as $p){

            $this->gift .= "::P{$p['numero']}::\n";
            $this->gift .= "{$p['pregunta']}\n";
            $this->gift .= "{\n";

            foreach($p['opciones'] as $letra => $opcion){
                $isCorrect = (strtolower($letra) === strtolower($p['respuesta']));
                $prefix = $isCorrect ? '=' : '#';
                $this->gift .= "    {$prefix}{$this->escapeGift($opcion)}";

                if($isCorrect && !empty($p['retroalimentacion'])){
                    $this->gift .= "\n    %%%FEEDBACK%%%{$p['retroalimentacion']}";
                }

                $this->gift .= "\n";
            }

            $this->gift .= "}\n\n";
        }
        if (!$nameFile) {
            /* $nameFile = './files/archivo_'.date('m_d_His').'.txt'; */
            $nameFile = FileService::builderFile('txt');
        }
        file_put_contents($nameFile, $this->gift);
        return count($preguntas);
    }
    public function convertQuestions($preguntas, $nameFile = null) {
        FileService::cleanOldFiles('./files/archivo_*.txt');
        $this->gift = '';

        foreach($preguntas as $p){
            if(!isset($p['tipo'])){
                continue;
            }
            switch($p['tipo']){
                case 'multichoice':
                    $this->addMultiChoice(null, null, $p);
                    break;
                case 'essay':
                    $this->addEssay(null, null, $p);
                    break;
                case 'truefalse':
                    $this->addTrueOrFalse(null, null, $p);
                    break;
            }
        }

        if(!$nameFile){
           /* $nameFile = './files/archivo_'.date('m_d_His').'.txt'; */
           $nameFile = FileService::builderFile('txt');
        }

        file_put_contents($nameFile, $this->gift);
        return count($preguntas);
    }
    public function addMultiChoice($type, $quiz, $p){

        $this->gift .= "::P{$p['numero']}::\n";
        $this->gift .= "{$p['pregunta']}\n";
        $this->gift .= "{\n";

        foreach($p['opciones'] as $letra => $opcion){
            $isCorrect = (strtolower($letra) === strtolower($p['respuesta']));
            $prefix = $isCorrect ? '=' : '~';
            $this->gift .= "    {$prefix}{$this->escapeGift($opcion)}";

            if($isCorrect && !empty($p['retroalimentacion'])){
                $this->gift .= "%%%Feedback%%%{$p['retroalimentacion']}";
            }

            $this->gift .= "\n";
        }

        $this->gift .= "}\n\n";
    }
    public function addEssay($type, $quiz, $p){
        $this->gift .= "::P{$p['numero']}::\n";
         $this->gift .= "{$this->escapeGift($p['pregunta'])}\n";
        /* $this->gift .= "{$p['pregunta']}\n"; */
        if (!empty($p['retroalimentacion'])) {
            $this->gift .= "{%%%Feedback%%%{$p['retroalimentacion']} }\n";
        } else {
             $this->gift .= "{ }\n";
        }
        $this->gift .= "\n\n";
    }
    public function addTrueOrFalse($type, $quiz, $p){
        $isTrue = ($p['respuesta'] === 'verdadero');

        $this->gift .= "::P{$p['numero']}::\n";
         $this->gift .= "{$this->escapeGift($p['pregunta'])}\n";
        /* $this->gift .= "{$p['pregunta']}\n"; */
        $this->gift .= "{" . ($isTrue ? 'TRUE' : 'FALSE') . "}";

        if(!empty($p['retroalimentacion'])){
            $this->gift .= "%%%Feedback%%%{$p['retroalimentacion']}";
        }

        $this->gift .= "\n\n";
    }

    private function escapeGift($text){
        $special = ['#', '=', '{', '}', '~', '%'];
        foreach($special as $char){
            $text = str_replace($char, '\\' . $char, $text);
        }
        return $text;
    }

}