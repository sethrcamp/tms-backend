<?php


use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;


class JsonResponseMiddleware implements MiddlewareInterface {

	public function process(Request $request, RequestHandler $handler): ResponseInterface {
		$response = $handler->handle($request);
		$existingContent = (string) $response->getBody();

		$response = new JsonResponse(new Response());
		$response->getBody()->write($existingContent);

		return $response;
	}
}