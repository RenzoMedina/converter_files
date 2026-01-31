<?php

namespace App\Factory;

use App\Services\Parser\PdfParser;
use App\Services\Parser\WordParser;

class ParseFactory{
    public static function make ($extension){
        return match (strtolower($extension)) {
            'pdf' => new PdfParser(),
            'doc','docx' => new WordParser(),
            default => throw new \InvalidArgumentException("Tipo de archivo no soportado: $extension")
        };
    }
}