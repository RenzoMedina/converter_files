<?php

namespace App\Services\Parser;

use Smalot\PdfParser\Parser;
use App\Converter\FormatConverter;
use App\Interface\ParserInterface;

class PdfParser implements ParserInterface{
    private $parser;
    private $formatConverter;
    public function __construct(){
        $this->parser = new Parser();
        $this->formatConverter = new FormatConverter();
    }
    /**
     * Summary of extracText
     * @param mixed $path
     * @return string
     */
    public function extracText($path){
        $pdf = $this->parser->parseFile($path);
        return $pdf->getText();
    }
    /**
     * Summary of parseIndicators
     * @param mixed $text
     * @param mixed $outputFormat
     * @return array Array
     */
    private function parseIndicators ($text, $outputFormat){
        $indicators = (new LogicParser())->transformWithIndicators($text);
        if($outputFormat === 'data') {
            return $indicators;
        }
        return $this->formatConverter->convertIndicators( $indicators, $outputFormat);
    }
    /**
     * Summary of parser
     * @param mixed $type
     * @param mixed $path
     * @param mixed $format
     * @return array Array
     */
    public function parser($type, $path, $format = 'xml') {
        $text = $this->extracText($path);
        return match ($type) {
            'old' => (new LogicParser())->transforOld($text),
            'new' => (new LogicParser())->transform($text),
            'indicators' => $this->parseIndicators($text, $format),
        };
    }

}