<?php 

namespace App\Services;

class FileService{
    /**
     * Limpa arquivos antigos baseados em um padrão fornecido.
     * @param mixed $pattern
     * @return void
     *
     */
    public function cleanOldFiles($pattern){
        $oldFiles = glob($pattern);
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
        $path = rtrim($_ENV['FILES_PATH'], '/');
        $prefix = $_ENV['FILE_PREFIX'];
        return $path . '/' . $prefix . '_' . date('mdHis') . '.xml';
    }
}