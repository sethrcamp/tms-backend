<?php


use Slim\Psr7\Request;
use Slim\Psr7\Response;

class TestController {

	public static function test(Request $request, Response $response, Array $args) : Response {
		$data = [
			"foo" => "bar"
		];

		$response = new JsonResponse($response);
		return $response->withJson($data);
	}

}