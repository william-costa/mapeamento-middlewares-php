<?php

ini_set("display_errors",true);

include __DIR__.'/vendor/autoload.php';

use App\Http\Requests\QueueMapRequestHandler;
use App\Http\Requests\ServerRequestFactory;

//MANIPULADOR DE FILAS MAPEADAS DE MIDDLEWARES
$app = new QueueMapRequestHandler(QueueMapRequestHandler::getMap('ApiDefaultMap'));

//RESPONSE
echo $app->handle(ServerRequestFactory::createServerRequestFromGlobals());
