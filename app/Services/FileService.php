<?php 

namespace App\Services;

class FileService{
    /**
     * Limpa arquivos antigos baseados em um padrão fornecido.
     * @param mixed $pattern
     * @return void
     *
     */
    public static function cleanOldFiles($path){
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
    public static function builderFile($format = 'xml'){
        $extension = match ($format){
                'txt' => 'txt',
                default => 'xml',
                };
        return './files/archivo_'.date('mdHis').'.'.$extension;
    }
    /**
     * Limpiara los archivos en la carpeta
     * @return void
     */
    public static function cleanGeneratedFiles() {
        $patterns = [
            './files/archivo_*',
            './files/indicador_*',
            './files/*'
        ];
        foreach($patterns as $pattern) {
            $files = glob($pattern);
            foreach($files as $file) {
                if(is_file($file)) {
                    unlink($file);
                }
            }
        }
    }
}