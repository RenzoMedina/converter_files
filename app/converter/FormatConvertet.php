<?php

namespace App\Converter;

use App\Build\GitfBuild;
use App\Build\XmlBuild;

class FormatConvertet{
    public function convertIndicators($indicadoresData, $format = 'xml')
    {
        if (!$indicadoresData['success']) {
            return [
                'success' => false,
                'message' => 'No hay datos para convertir'
            ];
        }

        $resultados = [];
        
        foreach ($indicadoresData['indicadores'] as $indicador) {
            $nombreArchivo = './files/indicador_' . $indicador['indicador'] . '_' . date('m_d_His');
            
            $cantidadPreguntas = 0;
            
            switch ($format) {
                case 'xml':
                    $nombreArchivo .= '.xml';
                    $xmlBuild = new XmlBuild();
                    $cantidadPreguntas = $xmlBuild->convertQuestions(
                        $indicador['preguntas'], 
                        $nombreArchivo
                    );
                    break;
                    
                case 'txt':
                    $nombreArchivo .= '.txt';
                    $txtBuild = new GitfBuild();
                    $cantidadPreguntas = $txtBuild->convertQuestions(
                        $indicador['preguntas'], 
                        $nombreArchivo
                    );
                    break;
                    
                default:
                    continue 2; 
            }
            
            $resultados[] = [
                'indicador' => $indicador['indicador'],
                'titulo' => $indicador['titulo'],
                'archivo' => basename($nombreArchivo),
                'cantidad' => $cantidadPreguntas,
                'formato' => $format
            ];
        }

        return [
            'success' => true,
            'archivos_generados' => count($resultados),
            'formato' => $format,
            'detalles' => $resultados
        ];
    }
}