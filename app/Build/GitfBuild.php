<?php

namespace App\Build;

use App\Interface\BuildInterface;

class GitfBuild implements BuildInterface{
    public function multiChoicesOld($preguntas, $nameFile = null){}
    public function convertQuestions($preguntas, $nameFile = null) {}
    public function addMultiChoice($type, $quiz, $p){}
    public function addEssay($type, $quiz, $p){}
    public function addTrueOrFalse($type, $quiz, $p){}

}