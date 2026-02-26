<?php 

use App\Controller\ConvertController;
use App\Services\FileService;

Flight::set('flight.views.path', './public/');
Flight::route("/", function(){
    Flight::render("index");
});

Flight::route( "POST /upload", [ConvertController::class,"convert"]);
Flight::route("GET /download",[ConvertController::class,"download"]);
Flight::map('notFound', function(){
    Flight::render("404");
});
Flight::map('error', function(Throwable $e){
    FileService::cleanGeneratedFiles();
    Flight::render("500");
});
Flight::map('methodNotFound', function($method){
    Flight::render("405");
});
Flight::start();