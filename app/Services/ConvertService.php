<?php 

namespace App\Services;

use App\Utils\ConvertUtils;

class ConvertService{

    private $parser;
    public function __construct(){
        $this->parser = new \Smalot\PdfParser\Parser();
    }
    /* public function transforNew($path){
            $pdf = $this->parser->parseFile($path);
            $text = $pdf->getText();

            $pattern = '/N° de pregunta:\s*(\d+)\s*(.*?)\nAlternativas\s*'
                    . 'a\)\s*(.*?)\n'
                    . 'b\)\s*(.*?)\n'
                    . 'c\)\s*(.*?)\n'
                    . 'd\)\s*(.*?)\n'
                    . 'e\)\s*(.*?)\n'
                    .'.*?Respuesta correcta\s*([aA-eE])\s*'
                    .'Retroalimentación:\s*(.*?)(?=N° de pregunta:|$)/s';
            preg_match_all($pattern, $text, $match, PREG_SET_ORDER);
        
            $preguntas =[];
            foreach($match as $m){
                
                $retroalimentacionLimpia = trim($m[9]);
                if(preg_match('/(.*?semana\.)/s', $retroalimentacionLimpia, $retroMatch)) {
                    $retroalimentacionLimpia = trim($retroMatch[1]);
                } else {
                    $retroalimentacionLimpia = preg_replace('/\n\s*\d+\s+.*$/s', '', $retroalimentacionLimpia);
                }
                $retroalimentacionLimpia = preg_replace('/\s+/', ' ', $retroalimentacionLimpia);
                
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
                    'retroalimentacion' =>$retroalimentacionLimpia
                ];
            }
         return (new ConvertUtils())->multiChoicesOld($preguntas);       
    } */

    public function transforOld($path){
        $pdf = $this->parser->parseFile($path);
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
       return (new ConvertUtils())->multiChoicesOld($preguntas);
    }

    public function transforWithIndicators($path){
        $pdf = $this->parser->parseFile($path);
        $text = $pdf->getText();
        
        // Dividir por indicadores
        $indicadores = $this->extractIndicators($text);
        
        $resultados = [];
        
        foreach($indicadores as $numIndicador => $contenidoIndicador) {
            $preguntas = $this->processIndicator($contenidoIndicador);
            
            // FILTRAR preguntas válidas
            $preguntasValidas = array_filter($preguntas, function($p){
                return isset($p['tipo']) && 
                    $p['tipo'] !== 'ERROR' && 
                    in_array($p['tipo'], ['multichoice', 'essay']);
            });
            
            // Reindexar array
            $preguntasValidas = array_values($preguntasValidas);
            
            if(empty($preguntasValidas)){
                continue; // Saltar indicadores sin preguntas válidas
            }
            
            // Generar archivo XML por indicador
            $nombreArchivo = './files/indicador_' . $numIndicador . '_' . date('m_d_His') . '.xml';
            $cantidadPreguntas = (new ConvertUtils())->convertQuestions($preguntasValidas, $nombreArchivo);
            
            $resultados[] = [
                'indicador' => $numIndicador,
                'titulo' => $contenidoIndicador['titulo'],
                'archivo' => basename($nombreArchivo),
                'cantidad' => $cantidadPreguntas
                ];
            }
        
        return [
            'success' => true,
            'archivos_generados' => count($resultados),
            'detalles' => $resultados
        ];
    }

    private function extractIndicators($text) {
        // Encontrar todas las posiciones de indicadores
        preg_match_all('/Indicador\s+(\d+):\s*([^\n]*)/s', $text, $matches, PREG_OFFSET_CAPTURE);
            
        $indicadores = [];
            
        for($i = 0; $i < count($matches[0]); $i++) {
            $numIndicador = (int)$matches[1][$i][0];
            $tituloIndicador = trim($matches[2][$i][0]);
            $inicioContenido = $matches[0][$i][1] + strlen($matches[0][$i][0]);
                
            // Determinar dónde termina este indicador
            if($i < count($matches[0]) - 1) {
                   $finContenido = $matches[0][$i + 1][1];
            } else {
                $finContenido = strlen($text);
            }
                
            $contenido = substr($text, $inicioContenido, $finContenido - $inicioContenido);
                
            $indicadores[$numIndicador] = [
                'titulo' => $tituloIndicador,
                'contenido' => $contenido
            ];
        }
            
        return $indicadores;
    }

    private function processIndicator($indicador) {
        $contenido = $indicador['contenido'];
    
        // Encontrar todas las preguntas con sus posiciones
        preg_match_all('/N° de pregunta:\s*(\d+)/s', $contenido, $matches, PREG_OFFSET_CAPTURE);
            
        $preguntas = [];
            
        for($i = 0; $i < count($matches[0]); $i++) {
            $numeroPregunta = (int)$matches[1][$i][0];
            $inicioPregunta = $matches[0][$i][1];
                
            // Determinar dónde termina esta pregunta
            if($i < count($matches[0]) - 1) {
                $finPregunta = $matches[0][$i + 1][1];
            } else {
                $finPregunta = strlen($contenido);
            }
                
            $bloque = substr($contenido, $inicioPregunta, $finPregunta - $inicioPregunta);
            $bloque = trim($bloque);
                
            // Detectar tipo: si tiene estructura a) b) c) d) e) es multichoice
            $tieneOpciones = preg_match('/Alternativas\s+a\)\s+.*?\s+b\)\s+.*?\s+c\)\s+.*?\s+d\)\s+.*?\s+e\)/s', $bloque);
                
            if($tieneOpciones) {
                $pregunta = $this->processMultiChoice($bloque);
            } else {
                $pregunta = $this->processEssay($bloque);
            }
                
            if($pregunta) {
                $preguntas[] = $pregunta;
            }
        }
            
        return $preguntas;
    }

    private function processMultiChoice($bloque) {
        $pattern = '/N° de pregunta:\s*(\d+)\s+'
                    . '(.*?)'  
                    . '\s*Alternativas\s+'
                    . 'a\)\s+(.*?)\s+'  
                    . 'b\)\s+(.*?)\s+'   
                    . 'c\)\s+(.*?)\s+'  
                    . 'd\)\s+(.*?)\s+'  
                    . 'e\)\s+(.*?)\s+'  
                    . 'Respuesta correcta\s+([a-eA-E])'  
                    . '(.*?)$'  
                    . '/s';
            
        if(preg_match($pattern, $bloque, $m)) {
            $resto = $m[9];
            $retroalimentacion = '';
                
            if(preg_match('/Retroalimentación:\s*(.*?)$/s', $resto, $retroMatch)) {
                $retroalimentacion = trim($retroMatch[1]);
            }

            $retroalimentacionLimpia = $this->cleanFeedback($retroalimentacion);
                
            return [
                'tipo' => 'multichoice',
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
                'retroalimentacion' => $retroalimentacionLimpia
            ];
        }
        return null;
    }

    private function processEssay($bloque) {
        // Patrón para essay (sin alternativas)
        $pattern = '/N° de pregunta:\s*(\d+)\s+'
            . '(.*?)'
            . '\s*Escribe aquí tu respuesta\s*'
            . '(?:Retroalimentación:\s*(.*?))?$'
            . '(?=\s*$)/s';
        
        if(preg_match($pattern, $bloque, $m)) {
            $retroalimentacion = isset($m[3]) ? trim($m[3]) : '';
            $retroalimentacionLimpia = $this->cleanFeedback($retroalimentacion);
            
            return [
                'tipo' => 'essay',
                'numero' => $m[1],
                'pregunta' => trim($m[2]),
                'retroalimentacion' => $retroalimentacionLimpia
            ];
        }
        
        return null;
    }

    private function cleanFeedback($texto) {
        if(empty($texto)) return '';
    
        $retroalimentacionLimpia = trim($texto);
        
        // Limpiar hasta "semana."
        if(preg_match('/(.*?semana\.)/s', $retroalimentacionLimpia, $retroMatch)) {
            $retroalimentacionLimpia = trim($retroMatch[1]);
            return trim(preg_replace('/\s+/', ' ', $retroMatch[1]));
        }
        // 2. Si no termina en "semana.", dividir por líneas y limpiar
        $lineas = explode("\n", $retroalimentacionLimpia);
        $lineasValidas = [];
        
        foreach($lineas as $linea) {
            $linea = trim($linea);
            
            // Saltar líneas vacías
            if(empty($linea)) {
                continue;
            }
            
            // Detener si encuentra solo números (número de página)
            if(preg_match('/^\d+$/', $linea)) {
                break;
            }
            
            // Detener si encuentra "Indicador" o "N° de pregunta"
            if(preg_match('/^(Indicador|N°\s*de\s*pregunta)/i', $linea)) {
                break;
            }
            
            // Agregar línea válida
            $lineasValidas[] = $linea;
        }
    
        // Unir las líneas válidas
        $retroalimentacionLimpia = implode(' ', $lineasValidas);
        
        // Normalizar espacios múltiples
        $retroalimentacionLimpia = preg_replace('/\s+/', ' ', $retroalimentacionLimpia);
        
        return trim($retroalimentacionLimpia);
    }

    public function transform($file){
        $pdf = $this->parser->parseFile($file);
        $pdfText = $pdf->getText();
        $pattern = '/N°\s*de\s+pregunta:\s*(\d+)\s*(.*?)\s*Retroalimentación:\s*(.*?)(?=N°\s*de\s+pregunta:|$)/s';
        preg_match_all($pattern, $pdfText, $matches, PREG_SET_ORDER);
        $questions = [];
        foreach($matches as $m){
            
        $feedback = trim($m[3]);
            if(preg_match('/(.*?semana\.)/s', $feedback, $retroMatch)) {
                    $feedback = trim($retroMatch[1]);
                } else {
                    $feedback = preg_replace('/\n\s*\d+\s+.*$/s', '', $feedback);
                }
        $feedback = preg_replace('/\s+/', ' ', $feedback);
        $answer = strtolower(trim(preg_replace('/.*Respuesta\s*correcta\s*/s','',$m[2])));

        $questions[] =[
                'tipo'=>$this->extractOptionsEssay($m[2], $this->extractOptionsMultichoices($m[2])[1]),
                'numero'=>$m[1],
                'pregunta'=>$this->cleanQuestion(trim($m[2])),
                'opciones'=>$this->extractOptionsMultichoices($m[2])[0],
                'respuesta'=>$this->cleanAnswer($answer),
                'retroalimentacion'=>$feedback,
            ];
        }
        return (new ConvertUtils())->convertXML($questions); ;
    }
    private function extractOptionsMultichoices($text,  $alternatives = [], $type=''){
        $alternativesPattern ='/Alternativas\s*(.*?)(?=Indicador\s+de\s+evaluación|Respuesta\s+correcta)/s';
        preg_match($alternativesPattern, $text, $matches);
        if (isset($matches[1])) {
            $textAlternatives = trim($matches[1]);

            $patterOptions = '/([a-e])\)\s*([^\n]+)/';
            preg_match_all($patterOptions, $textAlternatives, $optionMatches, PREG_SET_ORDER);
            foreach ($optionMatches as $opt) {
                $word = $opt[1];
                $content = trim($opt[2]);
                
                if (stripos($content, 'Alternativas') === false) {
                    $alternatives[$word] = $content;
                    $type = 'multichoice';
                }
            }
        }
        return [$alternatives, $type];
    }

    private function extractOptionsEssay($text, $type=''){
        $patternEssay = '/Escribe aquí tu respuesta/s';
        if (preg_match($patternEssay, $text)) {
            $type = 'essay';  
        }
        return $type;
    }

    private function cleanQuestion($text){
        $patterQuestion = preg_replace('/\s*Alternativas\s*.*$/si', '', $text);
        $patterQuestion = preg_replace('/\s*Escribe\s+aquí\s+tu\s+respuesta.*$/si', '', $patterQuestion);
        $patterQuestion = preg_replace('/\s+/', ' ', $patterQuestion);
        return trim($patterQuestion);
    }

    private function cleanAnswer($answer){
        $answer = trim($answer);
        if (preg_match('/^[A-Ea-e]$/', $answer)) {
            return strtolower($answer);
        }else {
            return $answer = '';
        }
    }
}