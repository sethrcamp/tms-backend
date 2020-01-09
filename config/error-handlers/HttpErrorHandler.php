<?php

use Psr\Http\Message\ResponseInterface;
use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpException;
use Slim\Exception\HttpForbiddenException;
use Slim\Exception\HttpMethodNotAllowedException;
use Slim\Exception\HttpNotFoundException;
use Slim\Exception\HttpNotImplementedException;
use Slim\Exception\HttpUnauthorizedException;
use Slim\Handlers\ErrorHandler;

class HttpErrorHandler extends ErrorHandler
{
    public const BAD_REQUEST = 'BAD_REQUEST';
    public const INSUFFICIENT_PRIVILEGES = 'INSUFFICIENT_PRIVILEGES';
    public const NOT_ALLOWED = 'NOT_ALLOWED';
    public const NOT_IMPLEMENTED = 'NOT_IMPLEMENTED';
    public const RESOURCE_NOT_FOUND = 'RESOURCE_NOT_FOUND';
    public const SERVER_ERROR = 'SERVER_ERROR';
    public const UNAUTHENTICATED = 'UNAUTHENTICATED';

    protected function respond(): ResponseInterface
    {
        $exception = $this->exception;
        $statusCode = 500;
        $type = self::SERVER_ERROR;
        $description = 'An internal error has occurred while processing your request.';
        $stackTrace = 'N/A';
        $line = 'N/A';
        $file = 'N/A';


        if ($exception instanceof HttpException) {
            $statusCode = $exception->getCode();
            $description = $exception->getMessage();
            $stackTrace = $exception->getTraceAsString();
            $line = $exception->getLine();
            $file = $exception->getFile();


            if ($exception instanceof HttpNotFoundException) {
                $type = self::RESOURCE_NOT_FOUND;
            } elseif ($exception instanceof HttpMethodNotAllowedException) {
                $type = self::NOT_ALLOWED;
            } elseif ($exception instanceof HttpUnauthorizedException) {
                $type = self::UNAUTHENTICATED;
            } elseif ($exception instanceof HttpForbiddenException) {
                $type = self::UNAUTHENTICATED;
            } elseif ($exception instanceof HttpBadRequestException) {
                $type = self::BAD_REQUEST;
            } elseif ($exception instanceof HttpNotImplementedException) {
                $type = self::NOT_IMPLEMENTED;
            }
        } else if ($exception instanceof Exception || $exception instanceof Throwable) {
            $description = $exception->getMessage();
            if (is_integer($exception->getCode()) && $exception->getCode() >= 100 && $exception->getCode() <= 599) {
                $statusCode = $exception->getCode();
            }
            $stackTrace = $exception->getTrace();
            $line = $exception->getLine();
            $file = $exception->getFile();
            $type = get_class($exception);
        }

        $error = [
            'statusCode' => $statusCode,
            'error' => [
                'type' => $type,
                'description' => $description
            ],
        ];

        if($this->displayErrorDetails) {
        	$error['error']['stackTrace'] = $stackTrace;
        	$error['error']['line'] = $line;
        	$error['error']['file'] = $file;
		}

        $payload = json_encode($error);

        $response = $this->responseFactory->createResponse($statusCode);
        $response->getBody()->write($payload);

        return $response->withHeader('Content-Type', 'application/json');
    }
}