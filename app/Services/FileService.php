<?php 

namespace App\Services;

class FileService{
    /**
     * Limpa arquivos antigos baseados em um padrão fornecido.
     * @param mixed $pattern
     * @return void
     *
     */
    public function cleanOldFiles($path){
        $oldFiles = glob($path);
        foreach($oldFiles as $oldFile){
            if (file_exists($oldFile)) {
                unlink($oldFile);
            }
        }
    }
    /**
     * Contruye o no el nombre del archivo basado en variables de entorno y la fecha actual.
     * @return string
     */
    public function builderFile(){
        return './files/archivo_'.date('mdHis').'.xml';
    }
}