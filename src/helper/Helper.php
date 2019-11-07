<?php


use Slim\Psr7\Response;

class Helper {
	public static function withJson(Response $response, Array $data) : Response {
		$response->getBody()->write(json_encode($data));
		return $response->withHeader('Content-Type', 'application/json');
	}
}