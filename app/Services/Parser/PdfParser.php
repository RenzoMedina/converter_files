<?php

namespace App\Services\Parser;

use App\Converter\FormatConvertet;
use Smalot\PdfParser\Parser;
use App\Interface\ParserInterface;

class PdfParser implements ParserInterface{
    private $parser;
    private $formatConverter;
    public function __construct(){
        $this->parser = new Parser();
        $this->formatConverter = new FormatConvertet();
    }

    private function extracText($path){
        $pdf = $this->parser->parseFile($path);
        return $pdf->getText();
    }

    private function parseIndicators ($text, $outputFormat){
        $indicators = (new LogicParser())->transformWithIndicators($text);
        if($outputFormat === 'data') {
            return $indicators;
        }
        return $this->formatConverter->convertIndicators( $indicators, $outputFormat);
    }
    public function parser($type, $path) {
        $text = $this->extracText($path);
        return match ($type) {
            'old' => new LogicParser()->transforOld($text),
            'new' => new LogicParser()->transform($text),
            'indicators' => $this->parseIndicators($text, $path),
        };
    }

}