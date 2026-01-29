<?php

namespace App\Services;

use App\Extractor\MultichoiceExtractor;
use App\Extractor\TrueFalseExtractor;
use App\Utils\RegexUtils;
use App\Utils\ConvertUtils;
use Smalot\PdfParser\Parser;
use App\Services\CleanServices;
use App\Extractor\EssayExtractor;
use App\Utils\CleanQuestionUtils;

class ConvertService
{

    private $parser;
    public function __construct()
    {
        $this->parser = new Parser();
    }
    public function transforOld($path)
    {
        $pdf = $this->parser->parseFile($path);
        $text = $pdf->getText();
        $text = preg_replace("/\r\n|\r/", "\n", $text);
        $text = preg_replace('/.*?plazos establecidos\.\s*/s', '', $text);
        $text = preg_replace('/^(?:[A-ZÁÉÍÓÚÜÑ0-9\s\.\-]+?\n){1,3}/', '', $text);

        $pattern = '/^\s*(\d+)\.\s*'
            . '(.*?) '
            . '\s+'
            . 'a\)\s*(.*?)\n'
            . 'b\)\s*(.*?)\n'
            . 'c\)\s*(.*?)\n'
            . 'd\)\s*(.*?)\n'
            . 'e\)\s*(.*?)\n'
            . '.*?Respuesta\s*correcta\s*'
            . 'Retroalimentación\s*'
            . '([aA-eE])'
            . '\s*'
            . '((?:(?!\n\s*\d+\.\s).)*)'
            . '/smx';

        preg_match_all($pattern, $text, $matches, PREG_SET_ORDER);

        $preguntas = [];

        foreach ($matches as $m) {
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
                'retroalimentacion' => trim($m[9]),
            ];
        }
        if (empty($preguntas)) {
            return 0;
        }
        return (new ConvertUtils())->multiChoicesOld($preguntas);
    }
    public function transformWithIndicators($path)
    {
        $pdf = $this->parser->parseFile($path);
        $text = $pdf->getText();

        // Dividir por indicadores
        $indicadores = $this->extractIndicators($text);

        $resultados = [];

        foreach ($indicadores as $numIndicador => $contenidoIndicador) {
            $preguntas = $this->processIndicator($contenidoIndicador);

            // FILTRAR preguntas válidas usando la función común
            $preguntasValidas = CleanServices::filterValidQuestions($preguntas);

            if (empty($preguntasValidas)) {
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

    public function transform($file)
    {
        $pdf = $this->parser->parseFile($file);
        $pdfText = $pdf->getText();
        $textClean = CleanServices::fullText($pdfText);
        // Extraer preguntas usando el patrón original
        $pattern = RegexUtils::QUESTION;
        preg_match_all($pattern, $textClean, $matches, PREG_SET_ORDER);

        $questions = [];
        foreach ($matches as $m) {
            // Procesar cada bloque como pregunta individual
            $bloque = $m[0];
            $pregunta = $this->processQuestion($bloque, $m);

            if ($pregunta) {
                $questions[] = $pregunta;
            }
        }

        if (empty($questions)) {
            return 0;
        }

        return (new ConvertUtils())->convertQuestions($questions);
    }

    /**
     * Procesa un bloque de pregunta y devuelve su estructura
     * @param mixed $bloque
     * @param mixed $matches
     * @return array{numero: mixed, opciones: mixed, pregunta: string, retroalimentacion: string, tipo: mixed|null}
     */
    private function processQuestion($bloque, $matches = null)
    {
        // Si no tenemos matches, extraerlos del bloque
        if ($matches === null) {
            $pattern = RegexUtils::PROCCESS_BLOCK;
            if (!preg_match($pattern, $bloque, $matches)) {
                return null;
            }
        }

        $numero = isset($matches[1]) ? $matches[1] : '';
        $contenido = isset($matches[2]) ? $matches[2] : '';
        $retroalimentacion = isset($matches[3]) ? $matches[3] : '';

        // Detectar tipo de pregunta
        $tipo = $this->categoryQuestion($contenido);

        if (empty($tipo)) {
            return null;
        }

        // Procesar según el tipo
        $pregunta = [
            'tipo' => $tipo,
            'numero' => $numero,
            'pregunta' => CleanQuestionUtils::clean($contenido),
            'opciones' => $this->getOptions($contenido, $tipo),
            'retroalimentacion' => CleanServices::feedback($retroalimentacion)
        ];

        // Agregar respuesta solo si el tipo lo requiere
        if ($tipo !== 'essay') {
            $respuesta = $this->extractAnswer($contenido);
            $pregunta['respuesta'] = CleanServices::answer($respuesta);
        }

        return $pregunta;
    }

    /**
     * Procesa un indicador y extrae sus preguntas
     * @param mixed $indicador
     * @return array{numero: mixed, opciones: mixed, pregunta: string, retroalimentacion: string, tipo: mixed[]}
     */
    private function processIndicator($indicador)
    {
        $contenido = $indicador['contenido'];

        // Encontrar todas las preguntas con sus posiciones
        preg_match_all(RegexUtils::PROCCESS_INDICATORS, $contenido, $matches, PREG_OFFSET_CAPTURE);

        $preguntas = [];

        for ($i = 0; $i < count($matches[0]); $i++) {
            $numeroPregunta = (int) $matches[1][$i][0];
            $inicioPregunta = $matches[0][$i][1];

            // Determinar dónde termina esta pregunta
            if ($i < count($matches[0]) - 1) {
                $finPregunta = $matches[0][$i + 1][1];
            } else {
                $finPregunta = strlen($contenido);
            }

            $bloque = substr($contenido, $inicioPregunta, $finPregunta - $inicioPregunta);
            $bloque = trim($bloque);

            // Usar la función común para procesar
            $pregunta = $this->processQuestionFromBlock($bloque);

            if ($pregunta) {
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
    private function processQuestionFromBlock($bloque)
    {
        // Extraer componentes del bloque
        $pattern = RegexUtils::PROCCESS_IMPORT_BLOCK;

        if (!preg_match($pattern, $bloque, $m)) {
            return null;
        }

        $numero = $m[1];
        $contenido = $m[2];
        $retroalimentacion = isset($m[3]) ? $m[3] : '';

        // Detectar tipo de pregunta usando función existente
        $tipo = $this->categoryQuestion($contenido);

        if (empty($tipo)) {
            return null;
        }

        // Construir pregunta
        $pregunta = [
            'tipo' => $tipo,
            'numero' => $numero,
            'pregunta' => CleanQuestionUtils::clean($contenido),
            'opciones' => $this->getOptions($contenido, $tipo),
            'retroalimentacion' => CleanServices::feedback($retroalimentacion)
        ];

        // Agregar respuesta si no es essay
        if ($tipo !== 'essay') {
            $respuesta = $this->extractAnswer($contenido);
            $pregunta['respuesta'] = CleanServices::answer($respuesta);
        }

        return $pregunta;
    }

    /**
     * Extrae la respuesta correcta del texto
     * @param mixed $text
     * @return string
     */
    private function extractAnswer($text)
    {
        $pattern = RegexUtils::CLEAN_ANSWER;
        if (preg_match($pattern, $text, $match)) {
            return strtolower(trim($match[1]));
        }
        return '';
    }

    /**
     * Extrae los indicadores y sus contenidos del texto
     * @param mixed $text
     * @return array{contenido: string, titulo: string[]}
     */
    private function extractIndicators($text)
    {
        preg_match_all(RegexUtils::EXTRACT_INDICATORS, $text, $matches, PREG_OFFSET_CAPTURE);

        $indicadores = [];

        for ($i = 0; $i < count($matches[0]); $i++) {
            $numIndicador = (int) $matches[1][$i][0];
            $tituloIndicador = trim($matches[2][$i][0]);
            $inicioContenido = $matches[0][$i][1] + strlen($matches[0][$i][0]);

            if ($i < count($matches[0]) - 1) {
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
    private function categoryQuestion($text)
    {
        list($alternativesTF, $typetrueOrFalse) = TrueFalseExtractor::optionsTrueFalse($text);
        if ($typetrueOrFalse !== '') {
            return $typetrueOrFalse;
        }

        $typeEssay = EssayExtractor::essay($text);
        if ($typeEssay !== '') {
            return $typeEssay;
        }

        list($alternatives, $typeMulti) = MultichoiceExtractor::optionsMultichoice($text);
        if ($typeMulti !== '') {
            return $typeMulti;
        }
        return '';
    }

    /**
     * Obtiene las opciones según el tipo de pregunta
     * @param mixed $text
     * @param mixed $type
     */
    private function getOptions($text, $type)
    {
        switch ($type) {
            case 'multichoice':
                list($opciones, $tipoTemp) = MultichoiceExtractor::optionsMultichoice($text);
                return $opciones;

            case 'truefalse':
                list($opciones, $tipoTemp) = TrueFalseExtractor::optionsTrueFalse($text);
                return $opciones;

            case 'essay':
            default:
                return [];
        }
    }

}