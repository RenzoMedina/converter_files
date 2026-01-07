<?php

namespace App\Controller;

use Flight;
use ZipArchive;
use App\Services\ConvertService;

class ConvertController{
    public function convert(){
        $upload = Flight::request()->getUploadedFiles()['documentFile'];
         if($upload->getClientMediaType() != "application/pdf"){
            Flight::redirect('/?error-file');
            die;
        } 
        if ($upload->getError() === UPLOAD_ERR_OK) {
            $path = './files/'.$upload->getClientFilename();
            $upload->moveTo($path);
            
            try{
                // Intentar con formato de indicadores
                $resultado = (new ConvertService())->transforWithIndicators($path);
                
                if(isset($resultado['success']) && $resultado['success'] === true && $resultado['archivos_generados'] > 0){
                    // Éxito con múltiples indicadores
                    $totalPreguntas = array_sum(array_column($resultado['detalles'], 'cantidad'));
                    Flight::redirect('/?success=1&indicadores='.$resultado['archivos_generados'].'&total='.$totalPreguntas);
                } else {
                    throw new \Exception('No se encontraron indicadores');
                }    
            } catch(\Exception $e) {
                // Fallback: intentar con formato antiguo
                try {
                    $text = (new ConvertService())->transforOld($path);
                    if($text > 0){
                        Flight::redirect('/?success=1&total='.urlencode((string)$text));
                    } else {
                        Flight::redirect('/?error=no-preguntas');
                    }
                } catch(\Exception $e2) {
                    Flight::redirect('/?error='.urlencode($e2->getMessage()));
                }
            }
            //primera formato
           /*  $textXML = (new ConvertService())->transforWithIndicators($path);
            if($textXML <= 0){
                //segundo formato
                $text = (new ConvertService())->transforOld($path);
                Flight::redirect('/?success=1&total='.urlencode((string)$text));
            } */
            /* else{
                Flight::redirect('/?success=1&total='.urlencode((string)$textXML));
            } */
        /* } else {
            Flight::redirect('/?error'); */
        }
        if (file_exists($path)) {
                unlink($path);
            }
    }
/*     public function download(){
        $files = glob('./files/archivo_*.xml');
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
            exit;
        } else {
            Flight::redirect('/?not-file');
        }
    } */
   public function download(){
    // Buscar todos los archivos generados
    $files = glob('./files/indicador_*.xml');
    
    if(empty($files)){
        Flight::redirect('/?not-file');
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
        Flight::redirect('/?error-zip');
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
        Flight::redirect('/?error-download');
    }
}

private function downloadSingleFile($file){
    if (file_exists($file)) {
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="'.basename($file).'"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($file));
        readfile($file);
        
        // Limpiar archivo después de descarga
        unlink($file);
        exit;
    }
}

}