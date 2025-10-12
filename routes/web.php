<?php 

use App\Controller\ConvertController;

Flight::set('flight.views.path', './public/');
Flight::route("/", function(){
    Flight::render("index");
});

Flight::route( "POST /upload", [ConvertController::class,"convert"]);
Flight::route("GET /download",[ConvertController::class,"download"]);

Flight::start();