<?php 
require 'vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__, null, false);
$dotenv->safeLoad();
require 'routes/web.php';

