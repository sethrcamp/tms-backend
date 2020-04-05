<?php


use Slim\Psr7\Response;

class JsonResponse extends Response {

	public function __construct(Response $response) {
		parent::__construct($response->status, $response->headers, $response->body);
	}

	public function withJson(Array $data) {
		$this->getBody()->write(json_encode($data));
		return $this->withHeader('Content-Type', 'application/json');
	}

}