<?php

namespace App\Controller;

use App\Services\ConvertService;
use Flight;

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
            //primera formato
            $textXML = (new ConvertService())->transforNew($path);
            if($textXML <= 0){
                //segundo formato
                $text = (new ConvertService())->transforNew($path);
                Flight::redirect('/?success=1&total='.urlencode((string)$text));
            }
            else{
                Flight::redirect('/?success=1&total='.urlencode((string)$textXML));
            }
        } else {
            Flight::redirect('/?error');
        }
        if (file_exists($path)) {
                unlink($path);
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