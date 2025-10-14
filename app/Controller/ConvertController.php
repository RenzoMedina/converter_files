<?php

namespace App\Controller;

use App\Services\ConvertService;
use Flight;

class ConvertController{
    public function convert(){
        $upload = Flight::request()->getUploadedFiles()['documentFile'];
        if ($upload->getError() === UPLOAD_ERR_OK) {
            $path = './files/'.$upload->getClientFilename();
            $upload->moveTo($path);
            (new ConvertService())->transfor($path);
            if (file_exists($path)) {
                unlink($path);
            }
            Flight::redirect('/?success');
        } else {
            Flight::redirect('/?error');
        }
    }
    public function download(){
        $file = './files/archivo.xml';
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
    }
}