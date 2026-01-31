<?php

namespace App\Interface;

interface BuildInterface {
    public function addMultiChoice($type, $quiz, $p);
    public function addEssay($type, $quiz, $p);
    public function addTrueOrFalse($type, $quiz, $p);
    public function multiChoicesOld($preguntas, $nameFile = null);
    public function convertQuestions($preguntas, $nameFile = null);
}