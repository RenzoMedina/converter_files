<?php

namespace App\Controller;

use App\Factory\BuildFactory;
use App\Factory\ParseFactory;
use App\Services\FileService;
use App\Services\Parser\FormatParser;
use Flight;
use ZipArchive;

class ConvertController{
    public function convert(){
        FileService::cleanGeneratedFiles();
        $upload = Flight::request()->getUploadedFiles()['documentFile'];
        $indicators = Flight::request()->data->indicador;
        $typeConverter = Flight::request()->data->typeformat;
        $mediaType = $upload->getClientMediaType();
        $validTypes = [
            'application/pdf',
            /* 'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' */
        ];
        if(!in_array($mediaType, $validTypes)){
            Flight::redirect('/?error=formato-invalido');
            die;
        }
        if ($upload->getError() === UPLOAD_ERR_OK) {
            $path = './files/'.$upload->getClientFilename();
            $upload->moveTo($path);
            $extension = pathinfo($path, PATHINFO_EXTENSION );

            $format = ParseFactory::make($extension);
            $typeParser = $format->extracText($path);
            $typeBuild = BuildFactory::make($typeConverter);
            $type = (new FormatParser())->format($typeParser);
            
            if($indicators == 'true'){
                try {
                    $resultado = $format->parser('indicators', $path, $typeConverter);
                    if(!$resultado['success'] || $resultado['archivos_generados'] === 0){
                        throw new \Exception('No se encontraron indicadores');
                    }
                    $totalPreguntas = array_sum(array_column($resultado['indicadores'], 'cantidad'));
                    $totalFallidas = 0;
                    $todasFallidas = [];
                    foreach ($resultado['indicadores'] as $indicador) {
                        if (!empty($indicador['fallidas'])) {
                            foreach($indicador['fallidas'] as $num){
                                $todasFallidas[] = $indicador['indicador'] . ',' . $num;
                            }
                        }
                    }
                    $totalFallidas = array_sum(array_map(fn($ind) => count($ind['fallidas']), $resultado['indicadores']));
                    Flight::redirect('/?success=1&indicadores='.$resultado['archivos_generados'].'&total='.$totalPreguntas.'&fallidas='.$totalFallidas.'&fallidas_list='.urlencode(implode('|', $todasFallidas)));
                }catch(\Exception $e) {
                    Flight::redirect('/?error=no-preguntas-indicadores');
                    FileService::cleanGeneratedFiles();
                }
            } else {
                try{
                    $resultado = $format->parser($type, $path);
                    if($resultado === 0){
                        Flight::redirect('/?error=sin-preguntas');
                        return;
                    }
                    if (is_array($resultado) && isset($resultado['preguntas'])) {
                        $preguntas = $resultado['preguntas'];
                        $fallidas = $resultado['fallidas'] ?? [];
                    }else {
                        $preguntas = $resultado;
                        $fallidas = [];
                    }
                    if ($type == "old"){
                        $countQuestion = $typeBuild->multiChoicesOld($preguntas);
                    } else if ($type == "new") {
                        $countQuestion = $typeBuild->convertQuestions($preguntas);
                    }
                    $totalFallidas = count($fallidas);
                    $fallidasList = implode('|', array_map(fn($f) => $f['indicador'] . ',' . $f['numPregunta'], $fallidas));
                    Flight::redirect('/?success=1&total='.urlencode((string)$countQuestion).'&fallidas='.$totalFallidas.'&fallidas_list='.urlencode($fallidasList));
                }catch(\Exception $e) {
                    Flight::redirect('/?error=sin-preguntas');
                    FileService::cleanGeneratedFiles();
                }
                
            }
        } 
         if (file_exists($path)) {
                unlink($path);
            } 
    }
   public function download(){
         $files = glob('./files/*');
        
        if(empty($files)){
            Flight::redirect('/?error=not-file');
            return;
        }
        
        // Si solo hay un archivo, descargarlo directamente
        if(count($files) === 1){
            $this->downloadSingleFile($files[0]);
            return;
        }
        
        // Si hay múltiples archivos, crear un ZIP
        $zipName = './files/cuestionario_'.date('m_d_His').'.zip';
        $zip = new ZipArchive();
        
        if ($zip->open($zipName, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
            Flight::redirect('/?error=error-zip');
            return;
        }
        
        // Ordenar archivos por nombre (indicador)
        sort($files);
        
        // Agregar cada archivo al ZIP
        foreach($files as $file){
            $zip->addFile($file, basename($file));
        }
        
        $zip->close();
        
        // Descargar el ZIP
        if (file_exists($zipName)) {
            header('Content-Description: File Transfer');
            header('Content-Type: application/zip');
            header('Content-Disposition: attachment; filename="'.basename($zipName).'"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($zipName));
            readfile($zipName);
            
            // Limpiar archivos temporales
            unlink($zipName);
            foreach($files as $file){
                unlink($file);
            } 
            exit;
        } else {
            Flight::redirect('/?error=error-download');
        }
}

private function downloadSingleFile($file){
        $files = glob('./files/*');
        usort($files, function($a, $b) {
            return filemtime($b) - filemtime($a);
        });

        $file = $files[0];
        if (file_exists($file)) {
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="'.basename($file).'"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($file));
            readfile($file);
           
            unlink($file);
            exit;
        } else {
            Flight::redirect('/?error=not-file');
        }
}

}