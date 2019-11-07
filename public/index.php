<?php
/**
 * Created by PhpStorm.
 * User: Drew
 * Date: 2019-09-26
 * Time: 09:56
 */

use Slim\Factory\AppFactory;
use Slim\Factory\ServerRequestCreatorFactory;
use Slim\Psr7\Request;

$displayErrorDetails = true;
ini_set('display_errors', 1);

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . '/../config/error-handlers/HttpErrorHandler.php';
require_once __DIR__ . '/../config/error-handlers/ShutdownHandler.php';
require_once __DIR__ . '/../config/JsonBodyParserMiddleware.php';
require_once __DIR__ . '/../config/JsonResponseMiddleware.php';

$app = AppFactory::create();
$callableResolver = $app->getCallableResolver();
$responseFactory = $app->getResponseFactory();

$pathToPublic = "/" . basename(dirname(__DIR__, 1)) . "/public";
$app->setBasePath($pathToPublic);


$app->add(JsonBodyParserMiddleware::class);





$serverRequestCreator = ServerRequestCreatorFactory::create();
$request = $serverRequestCreator->createServerRequestFromGlobals();

$errorHandler = new HttpErrorHandler($callableResolver, $responseFactory);
$shutdownHandler = new ShutdownHandler($request, $errorHandler, $displayErrorDetails);
register_shutdown_function($shutdownHandler);

$errorMiddleware = $app->addErrorMiddleware(true, false, false);
$errorMiddleware->setDefaultErrorHandler($errorHandler);




$directoriesToIncludeInSrc = [
    "views",
    "controllers",
    "models",
	"helper"
];
foreach ($directoriesToIncludeInSrc as $directory) {
    $files = scandir("../src/$directory");
    foreach ($files as $file) {
        if (!is_dir($file)) {
            require_once __DIR__ . "/../src/$directory/$file";
        }
    }
}


$app->options('/{routes:.+}', function ($request, $response, $args) {
    return $response;
});

$app->run();