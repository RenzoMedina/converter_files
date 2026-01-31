<?php

namespace App\Services\Parser;

use PhpOffice\PhpWord\IOFactory;
use App\Converter\FormatConverter;
use App\Interface\ParserInterface;
use App\Services\Parser\LogicParser;

class WordParser implements ParserInterface{
    private $formatConverter;
    public function __construct(){
        $this->formatConverter = new FormatConverter();
    }
    public function extracText($path){
        $word = IOFactory::load($path);
        $text = '';

        foreach ($word->getSections() as $section) {
            $text .= $this->extractFromElements($section->getElements());
        }
        return $text;
    }
    private function extractFromElements($elements){
    $text = '';
    
        foreach ($elements as $element) {
            $class = get_class($element);
            
            // TextRun (texto con formato)
            if (method_exists($element, 'getElements')) {
                foreach ($element->getElements() as $childElement) {
                    if (method_exists($childElement, 'getText')) {
                        $text .= $childElement->getText();
                    }
                }
                $text .= "\n";
            }
            // Text simple
            elseif (method_exists($element, 'getText')) {
                $text .= $element->getText() . "\n";
            }
            // Table (tablas)
            elseif (method_exists($element, 'getRows')) {
                foreach ($element->getRows() as $row) {
                    foreach ($row->getCells() as $cell) {
                        $text .= $this->extractFromElements($cell->getElements());
                    }
                    $text .= "\n";
                }
            }
            // ListItem (listas)
            elseif (method_exists($element, 'getTextObject')) {
                $textObj = $element->getTextObject();
                if (method_exists($textObj, 'getText')) {
                    $text .= $textObj->getText() . "\n";
                }
            }
        }
        return $text;
    }
    private function parseIndicators($text, $outputFormat){
        $indicators = (new LogicParser())->transformWithIndicators($text);
        if($outputFormat === 'data') {
            return $indicators;
        }
        return $this->formatConverter->convertIndicators($indicators, $outputFormat);
    }

    public function parser($type, $path, $format = 'xml'){
        $text = $this->extracText($path);
        return match ($type) {
            'old' => (new LogicParser())->transforOld($text),
            'new' => (new LogicParser())->transform($text),
            'indicators' => $this->parseIndicators($text, $format),
        };
    }
}