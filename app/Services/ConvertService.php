<?php 

namespace App\Services;

use DOMDocument;

class ConvertService{

    public function transfor($path){
            $parser = new \Smalot\PdfParser\Parser();
            $pdf = $parser->parseFile($path);
            $text = $pdf->getText();

            $pattern = '/N° de pregunta:\s*(\d+)\s*(.*?)\nAlternativas\s*'
                    . 'a\)\s*(.*?)\n'
                    . 'b\)\s*(.*?)\n'
                    . 'c\)\s*(.*?)\n'
                    . 'd\)\s*(.*?)\n'
                    . 'e\)\s*(.*?)\n'
                    .'.*?Respuesta correcta\s*([A-E])\s*'
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

            // Crear XML para Moodle
            $xml = new DOMDocument('1.0', 'UTF-8');
            $xml->formatOutput = true;

            // Elemento raíz
            $quiz = $xml->createElement('quiz');
            $xml->appendChild($quiz);

            // Mapeo de letras a opciones
            $letraMap = ['a', 'b', 'c', 'd', 'e'];

            foreach($preguntas as $p){
                // Crear pregunta
                $question = $xml->createElement('question');
                $question->setAttribute('type', 'multichoice');
                
                // Nombre de la pregunta
                $name = $xml->createElement('name');
                $nameText = $xml->createElement('text');
                $nameText->appendChild($xml->createTextNode('P' . $p['numero']));
                $name->appendChild($nameText);
                $question->appendChild($name);
                
                // Texto de la pregunta
                $questiontext = $xml->createElement('questiontext');
                $questiontext->setAttribute('format', 'html');
                $questiontextText = $xml->createElement('text');
                $questiontextText->appendChild($xml->createCDATASection(htmlspecialchars($p['pregunta'])));
                $questiontext->appendChild($questiontextText);
                $question->appendChild($questiontext);
                
                // Retroalimentación general
                $generalfeedback = $xml->createElement('generalfeedback');
                $generalfeedback->setAttribute('format', 'html');
                $generalfeedbackText = $xml->createElement('text');
                $generalfeedback->appendChild($generalfeedbackText);
                $question->appendChild($generalfeedback);
                
                // Penalización por intento
                $penalty = $xml->createElement('penalty', '0.3333333');
                $question->appendChild($penalty);
                
                // Ocultar pregunta
                $hidden = $xml->createElement('hidden', '0');
                $question->appendChild($hidden);
                
                // Una sola respuesta
                $single = $xml->createElement('single', 'true');
                $question->appendChild($single);
                
                // No barajar respuestas
                $shuffleanswers = $xml->createElement('shuffleanswers', 'false');
                $question->appendChild($shuffleanswers);
                
                // Numerar respuestas
                $answernumbering = $xml->createElement('answernumbering', 'abc');
                $question->appendChild($answernumbering);
                
                // puntaje por defecto a 0.5
                $defaultgrade = $xml->createElement('defaultgrade', '0.5');
                $question->appendChild($defaultgrade);

                // No mostrar instrucción estándar
                $showStandardInstruction = $xml->createElement('showstandardinstruction', '0');
                $question->appendChild($showStandardInstruction);

                // Agregar opciones
                foreach($letraMap as $letra){
                    $answer = $xml->createElement('answer');
                    $correct = $letra === $p['respuesta'];
                    $fraction = ($letra === $p['respuesta']) ? '100' : '0';
                    $answer->setAttribute('fraction', $fraction);
                    $answer->setAttribute('format', 'html');
                    
                    $answerText = $xml->createElement('text');
                    $answerText->appendChild($xml->createCDATASection(htmlspecialchars($p['opciones'][$letra])));
                    $answer->appendChild($answerText);
                    
                    $feedback = $xml->createElement('feedback');
                    $feedback->setAttribute('format', 'html');
                    $feedbackText = $xml->createElement('text');
                    if($correct){
                        $feedbackText->appendChild($xml->createCDATASection($p['retroalimentacion']));
                    }
                    $feedback->appendChild($feedbackText);
                    $answer->appendChild($feedback);
                    
                    $question->appendChild($answer);
                }
                
                $quiz->appendChild($question);
            }

            $xml->save( './files/archivo.xml');
            return "Total de preguntas: " . count($preguntas);    
    }
}