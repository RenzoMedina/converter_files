<?php 

namespace App\Services;

use App\Utils\ConvertUtils;
use Smalot\PdfParser\Parser;

class ConvertService{
    
    private $parser;
    public function __construct(){
        $this->parser = new Parser();
    }
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
            if(empty($preguntas)){
                return 0;
            }
       return (new ConvertUtils())->multiChoicesOld($preguntas);
    }
    public function transformWithIndicators($path){
        $pdf = $this->parser->parseFile($path);
        $text = $pdf->getText();
        
        // Dividir por indicadores
        $indicadores = $this->extractIndicators($text);
        
        $resultados = [];
        
        foreach($indicadores as $numIndicador => $contenidoIndicador) {
            $preguntas = $this->processIndicator($contenidoIndicador);
            
            // FILTRAR preguntas válidas usando la función común
            $preguntasValidas = $this->filterValidQuestions($preguntas);
            
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

    public function transform($file){
        $pdf = $this->parser->parseFile($file);
        $pdfText = $pdf->getText();
        
        // Extraer preguntas usando el patrón original
        $pattern = '/N°\s*de\s+pregunta:\s*(\d+)\s*(.*?)\s*Retroalimentación:\s*(.*?)(?=N°\s*de\s+pregunta:|$)/s';
        preg_match_all($pattern, $pdfText, $matches, PREG_SET_ORDER);
        
        $questions = [];
        foreach($matches as $m){
            // Procesar cada bloque como pregunta individual
            $bloque = $m[0];
            $pregunta = $this->processQuestion($bloque, $m);
            
            if($pregunta){
                $questions[] = $pregunta;
            }
        }
        
        if(empty($questions)){
            return 0;
        }
        
        return (new ConvertUtils())->convertQuestions($questions);
    }

    /**
     *  Filtra y devuelve solo las preguntas válidas
     * 
     * @param mixed $preguntas
     * @return array
     */
    private function filterValidQuestions($preguntas) {
        $preguntasValidas = array_filter($preguntas, function($p){
            return isset($p['tipo']) && 
                $p['tipo'] !== 'ERROR' && 
                in_array($p['tipo'], ['multichoice', 'essay', 'truefalse']);
        });
        
        
        return array_values($preguntasValidas);
    }

    /**
     * Procesa un bloque de pregunta y devuelve su estructura
     * @param mixed $bloque
     * @param mixed $matches
     * @return array{numero: mixed, opciones: mixed, pregunta: string, retroalimentacion: string, tipo: mixed|null}
     */
    private function processQuestion($bloque, $matches = null) {
        // Si no tenemos matches, extraerlos del bloque
        if($matches === null) {
            $pattern = '/N°\s*de\s+pregunta:\s*(\d+)\s*(.*?)\s*Retroalimentación:\s*(.*?)$/s';
            if(!preg_match($pattern, $bloque, $matches)) {
                return null;
            }
        }
        
        $numero = isset($matches[1]) ? $matches[1] : '';
        $contenido = isset($matches[2]) ? $matches[2] : '';
        $retroalimentacion = isset($matches[3]) ? $matches[3] : '';
        
        // Detectar tipo de pregunta
        $tipo = $this->categoryQuestion($contenido);
        
        if(empty($tipo)) {
            return null;
        }
        
        // Procesar según el tipo
        $pregunta = [
            'tipo' => $tipo,
            'numero' => $numero,
            'pregunta' => $this->cleanQuestion($contenido),
            'opciones' => $this->getOptions($contenido, $tipo),
            'retroalimentacion' => $this->cleanFeedback($retroalimentacion)
        ];
        
        // Agregar respuesta solo si el tipo lo requiere
        if($tipo !== 'essay') {
            $respuesta = $this->extractAnswer($contenido);
            $pregunta['respuesta'] = $this->cleanAnswer($respuesta);
        }
        
        return $pregunta;
    }

    /**
     * Procesa un indicador y extrae sus preguntas
     * @param mixed $indicador
     * @return array{numero: mixed, opciones: mixed, pregunta: string, retroalimentacion: string, tipo: mixed[]}
     */
    private function processIndicator($indicador) {
        $contenido = $indicador['contenido'];

        // Encontrar todas las preguntas con sus posiciones
        preg_match_all('/N°\s*de\s*pregunta:\s*(\d+)/s', $contenido, $matches, PREG_OFFSET_CAPTURE);
        
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
            
            // Usar la función común para procesar
            $pregunta = $this->processQuestionFromBlock($bloque);
            
            if($pregunta) {
                $preguntas[] = $pregunta;
            }
        }
        
        return $preguntas;
    }

    /**
     * Procesa un bloque de pregunta y devuelve su estructura
     * @param mixed $bloque
     * @return array{numero: mixed, opciones: mixed, pregunta: string, retroalimentacion: string, tipo: mixed|null}
     */
    private function processQuestionFromBlock($bloque) {
        // Extraer componentes del bloque
        $pattern = '/N°\s*de\s*pregunta:\s*(\d+)\s+(.*?)(?:\s*Retroalimentación:\s*(.*?))?$/s';
        
        if(!preg_match($pattern, $bloque, $m)) {
            return null;
        }
        
        $numero = $m[1];
        $contenido = $m[2];
        $retroalimentacion = isset($m[3]) ? $m[3] : '';
        
        // Detectar tipo de pregunta usando función existente
        $tipo = $this->categoryQuestion($contenido);
        
        if(empty($tipo)) {
            return null;
        }
        
        // Construir pregunta
        $pregunta = [
            'tipo' => $tipo,
            'numero' => $numero,
            'pregunta' => $this->cleanQuestion($contenido),
            'opciones' => $this->getOptions($contenido, $tipo),
            'retroalimentacion' => $this->cleanFeedback($retroalimentacion)
        ];
        
        // Agregar respuesta si no es essay
        if($tipo !== 'essay') {
            $respuesta = $this->extractAnswer($contenido);
            $pregunta['respuesta'] = $this->cleanAnswer($respuesta);
        }
        
        return $pregunta;
    }

    /**
     * Extrae la respuesta correcta del texto
     * @param mixed $text
     * @return string
     */
    private function extractAnswer($text) {
        $pattern = '/Respuesta\s*correcta\s*([a-e]|verdadero|falso)/si';
        if(preg_match($pattern, $text, $match)) {
            return strtolower(trim($match[1]));
        }
        return '';
    }

    /**
     * Extrae los indicadores y sus contenidos del texto
     * @param mixed $text
     * @return array{contenido: string, titulo: string[]}
     */
    private function extractIndicators($text) {
        preg_match_all('/Indicador\s+(\d+):\s*([^\n]*)/s', $text, $matches, PREG_OFFSET_CAPTURE);
        
        $indicadores = [];
        
        for($i = 0; $i < count($matches[0]); $i++) {
            $numIndicador = (int)$matches[1][$i][0];
            $tituloIndicador = trim($matches[2][$i][0]);
            $inicioContenido = $matches[0][$i][1] + strlen($matches[0][$i][0]);
            
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


    /**
     * Categoriza el tipo de pregunta basado en su contenido
     * @param mixed $text
     */
    private function categoryQuestion($text){
        list($alternativesTF, $typetrueOrFalse) = $this->extractOptionsTrueFalse($text);
        if($typetrueOrFalse !== ''){
            return $typetrueOrFalse;
        }

        $typeEssay = $this->extractOptionsEssay($text);
        if($typeEssay !== ''){
            return $typeEssay;
        }

        list($alternatives, $typeMulti) = $this->extractOptionsMultichoices($text);
        if($typeMulti !== ''){
            return $typeMulti;
        }
        return '';
    }

    /**
     * Extrae las opciones de una pregunta de opción múltiple
     * @param mixed $text
     * @param mixed $alternatives
     * @param mixed $type
     * @return array
     */
    private function extractOptionsMultichoices($text, $alternatives = [], $type=''){
        $alternativesPattern ='/Alternativas\s*(.*?)(?=Indicador\s+de\s+evaluación|Respuesta\s+correcta)/s';
        preg_match($alternativesPattern, $text, $matches);
        if (isset($matches[0])) {
            $textAlternatives = trim($matches[0]);

            $patterOptions = '/([a-e])\)\s*([^\n]+)/';
            preg_match_all($patterOptions, $textAlternatives, $optionMatches, PREG_SET_ORDER);
            foreach ($optionMatches as $opt) {
                $word = strtolower($opt[1]);
                $content = trim($opt[2]);
                
                if (stripos($content, 'Alternativas') === false) {
                    $alternatives[$word] = $content;
                    $type = 'multichoice';
                }
            }
        }
        return [$alternatives, $type];
    }

    /**
     * Extrae las opciones de una pregunta de ensayo
     * @param mixed $text
     * @param mixed $type
     */
    private function extractOptionsEssay($text, $type=''){
        $patternEssay = '/Escribe aquí tu respuesta/s';
        if (preg_match($patternEssay, $text)) {
            $type = 'essay';  
        } 
        return $type;
    }

    /**
     * Extrae las opciones de una pregunta de verdadero/falso
     * @param mixed $text
     * @param mixed $alternatives
     * @param mixed $type
     * @return array
     */
    private function extractOptionsTrueFalse($text, $alternatives = [], $type=''){
        $patternTrueFalse = '/Verdadero\s+o\s+falso\s*(.*?)(?=Respuesta\s+correcta)/si';
        preg_match($patternTrueFalse, $text, $matches);
        if(isset($matches[1])){
            $textAlternatives = trim($matches[1]);

            $patterOptions = '/([a-b])\)\s*([^\n]+)/';
            preg_match_all($patterOptions, $textAlternatives, $optionMatches, PREG_SET_ORDER);
            foreach ($optionMatches as $opt) {
                $word = $opt[1];
                $content = trim($opt[2]);
                
                if (stripos($content, 'Verdadero o falso') === false) {
                    $alternatives[$word] = $content;
                    $type = 'truefalse';
                }
            }
        }
        return [$alternatives, $type];
    }

    /**
     * Limpia el texto de la pregunta eliminando partes innecesarias
     * @param mixed $text
     * @return string
     */
    private function cleanQuestion($text){
        $patterQuestion = preg_replace('/\n+/', '', $text);
        $patterQuestion = preg_replace('/\s+Alternativas\s+[a-e]\).*$/is', '', $text);
        $patterQuestion = preg_replace('/\s*Escribe\s+aquí\s+tu\s+respuesta.*$/si', '', $patterQuestion);
        $patterQuestion = preg_replace('/\s+Verdadero\s+o\s+falso\s+[a-b]\).*$/is', '', $patterQuestion);
        $patterQuestion = preg_replace('/\s+/', ' ', $patterQuestion);
        return trim($patterQuestion);
    }
    /**
     * Limpia la respuesta correcta
     * @param mixed $answer
     * @return string
     */
    private function cleanAnswer($answer){
        $answer = trim($answer);
        
        if (preg_match('/^[A-Ea-e]$/', $answer)) {
            return strtolower($answer);
        } elseif(preg_match('/^(verdadero|falso)$/i', $answer)) {
            return strtolower($answer);
        } else {
            return '';
        }
    }

    /**
     * Obtiene las opciones según el tipo de pregunta
     * @param mixed $text
     * @param mixed $type
     */
    private function getOptions($text, $type){
        switch($type){
            case 'multichoice':
                list($opciones, $tipoTemp) = $this->extractOptionsMultichoices($text);
                return $opciones;
                
            case 'truefalse':
                list($opciones, $tipoTemp) = $this->extractOptionsTrueFalse($text);
                return $opciones;
                
            case 'essay':
            default:
                return [];
        }
    }

    /**
     * Limpia la retroalimentación eliminando partes innecesarias
     * @param mixed $texto
     * @return string
     */
    private function cleanFeedback($texto) {
        if(empty($texto)) return '';

        $retroalimentacionLimpia = trim($texto);
        
        // Limpiar hasta "semana."
        if(preg_match('/(.*?semana\.)/s', $retroalimentacionLimpia, $retroMatch)) {
            return trim(preg_replace('/\s+/', ' ', $retroMatch[1]));
        }
        
        // Si no termina en "semana.", dividir por líneas y limpiar
        $lineas = explode("\n", $retroalimentacionLimpia);
        $lineasValidas = [];
        
        foreach($lineas as $linea) {
            $linea = trim($linea);
            
            if(empty($linea)) {
                continue;
            }
            
            if(preg_match('/^\d+$/', $linea)) {
                break;
            }
            
            if(preg_match('/^(Indicador|N°\s*de\s*pregunta)/i', $linea)) {
                break;
            }
            
            $lineasValidas[] = $linea;
        }

        $retroalimentacionLimpia = implode(' ', $lineasValidas);
        $retroalimentacionLimpia = preg_replace('/\s+/', ' ', $retroalimentacionLimpia);
        
        return trim($retroalimentacionLimpia);
    }

    private function cleanFullText($content){
        $pregunta = preg_split('/(N°\s*de\s*pregunta:\s*\d+)/i', $content, -1, PREG_SPLIT_DELIM_CAPTURE);
        $textoLimpio = '';
        for ($i = 1; $i < count($pregunta); $i +=2) {
            if(isset($pregunta[$i]) && isset($pregunta[$i + 1])) {
                $numeroPregunta = $pregunta[$i];
                $contenidoPregunta = $pregunta[$i+1];
            }

            $contenidoPregunta = $this->cleanQuestionBlock($contenidoPregunta);

            $textoLimpio .=$numeroPregunta.$contenidoPregunta."\n\n";
        }
        return trim($textoLimpio);
    }

    private function cleanQuestionBlock($block){
        if(preg_match('/(.*?Retroalimentación:.*?)(?=\n\s*\d+\s+[A-Z]|$)/s', $block, $matches)) {
            return trim($matches[1]);
        }
        return trim($block);
    }
}