<?php


use Predis\Autoloader;
use Predis\Client;
use Predis\Connection\ConnectionException;
use MongoDB;



try {
    Predis\Autoloader::register();
}catch(Exception $e){
    echo $e->getMessage();
}

try {
    $GLOBALS['REDIS'] = new Predis\Client();
    $GLOBALS['REDIS'] = new Predis\Client(array(
        "scheme" => "tcp",
        "host" => REDIS_HOST,
        "port" => 6379
    ));

    $GLOBALS['REDIS']->connect();

} catch (Predis\Connection\ConnectionException $exception) {
    pretty_print_r($exception);
}


//$m = new MongoDB\Client("mongodb://localhost:27017");
//
//
//$db = $m->api;
//$GLOBALS['mongodb']=$db;