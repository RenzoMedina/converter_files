<?php 

namespace App\Utils;

use DOMDocument;

class ConvertUtils{

    public function multiChoicesOld($preguntas, $nameFile = null){
            // Eliminar archivos antiguos
            $oldFiles = glob('./files/archivo_*.xml');
            foreach($oldFiles as $oldFile){
                if (file_exists($oldFile)) {
                    unlink($oldFile);
                }
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
            if(!$nameFile){
                $nameFile = './files/archivo_'.date('m_d_His').'.xml';
            }
            $xml->save($nameFile);
            return count($preguntas); 
    }

    public function convertirPreguntas($preguntas, $nameFile = null){
        // Eliminar archivos antiguos
        $oldFiles = glob('./files/archivo_*.xml');
        foreach($oldFiles as $oldFile){
            if (file_exists($oldFile)) {
                unlink($oldFile);
            }
        }
        
        // Crear XML para Moodle
        $xml = new DOMDocument('1.0', 'UTF-8');
        $xml->formatOutput = true;

        // Elemento raíz
        $quiz = $xml->createElement('quiz');
        $xml->appendChild($quiz);

        foreach($preguntas as $p){
            if(!isset($p['tipo'])){
                continue; // Saltar preguntas sin tipo
            }
            if($p['tipo'] === 'multichoice'){
                $this->agregarMultiChoice($xml, $quiz, $p);
            } elseif($p['tipo'] === 'essay'){
                $this->agregarEssay($xml, $quiz, $p);
            }
        }
        
        if(!$nameFile){
            $nameFile = './files/archivo_'.date('m_d_His').'.xml';
        }
        $xml->save($nameFile);
        return count($preguntas);
    }

    private function agregarMultiChoice($xml, $quiz, $p){
        $letraMap = ['a', 'b', 'c', 'd', 'e'];
        
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

    private function agregarEssay($xml, $quiz, $p){
        // Crear pregunta tipo essay
        $question = $xml->createElement('question');
        $question->setAttribute('type', 'essay');
        
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
        $generalfeedbackText->appendChild($xml->createCDATASection($p['retroalimentacion']));
        $generalfeedback->appendChild($generalfeedbackText);
        $question->appendChild($generalfeedback);
        
        // puntaje por defecto a 0.5
        $defaultgrade = $xml->createElement('defaultgrade', '0.5');
        $question->appendChild($defaultgrade);
        
        // Penalización
        $penalty = $xml->createElement('penalty', '0.0000000');
        $question->appendChild($penalty);
        
        // Ocultar pregunta
        $hidden = $xml->createElement('hidden', '0');
        $question->appendChild($hidden);
        
        // Formato de respuesta (editor HTML)
        $responseformat = $xml->createElement('responseformat', 'editor');
        $question->appendChild($responseformat);
        
        // Respuesta requerida
        $responserequired = $xml->createElement('responserequired', '1');
        $question->appendChild($responserequired);
        
        // Líneas del campo de respuesta
        $responsefieldlines = $xml->createElement('responsefieldlines', '15');
        $question->appendChild($responsefieldlines);
        
        // Adjuntos permitidos
        $attachments = $xml->createElement('attachments', '0');
        $question->appendChild($attachments);
        
        // Adjuntos requeridos
        $attachmentsrequired = $xml->createElement('attachmentsrequired', '0');
        $question->appendChild($attachmentsrequired);
        
        // Información para el calificador (vacío)
        $graderinfo = $xml->createElement('graderinfo');
        $graderinfo->setAttribute('format', 'html');
        $graderinfoText = $xml->createElement('text');
        $graderinfo->appendChild($graderinfoText);
        $question->appendChild($graderinfo);
        
        // Plantilla de respuesta (vacío)
        $responsetemplate = $xml->createElement('responsetemplate');
        $responsetemplate->setAttribute('format', 'html');
        $responsetemplateText = $xml->createElement('text');
        $responsetemplate->appendChild($responsetemplateText);
        $question->appendChild($responsetemplate);
        
        $quiz->appendChild($question);
    }

    // Mantener método original para compatibilidad
    public function multiChoices($preguntas, $nameFile = null){
        return $this->convertirPreguntas($preguntas, $nameFile);
    }


}