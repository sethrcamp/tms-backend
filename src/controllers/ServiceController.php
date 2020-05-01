<?php

use Slim\Psr7\Request;
use Slim\Psr7\Response;

class ServiceController {

	public static function getAll(Request $request, Response $response, array $args) {
		$services = Service::getAll();
		$result = [
			"services" => $services
		];

		$response = new JsonResponse($response);
		return $response->withJson($result);
	}

	public static function getById(Request $request, Response $response, array $args) {
		$service = Service::getById($args['id']);

		if($service === null) {
			throw new ItemNotFoundException("service", "id: ".$args['id']);
		}

		$result = [
			"service" => $service
		];

		$response = new JsonResponse($response);
		return $response->withJson($result);
	}

	public static function getAllForIsPrivate(Request $request, Response $response, array $args) {

		$services = Service::getAllByIsPrivate($args['is_private']);

		$result = [
			"services" => $services
		];

		$response = new JsonResponse($response);
		return $response->withJson($result);
	}

	public static function create(Request $request, Response $response, array $args) {
		$body = $request->getParsedBody();

		$required_parameters = [
			'name'
		];

		Helper::checkForAllParameters($body, $required_parameters);

		$service_with_name = Service::getByName($body['name']);

		if($service_with_name) {
			throw new ItemAlreadyExistsException("service", "name: ".$body['name']);
		}

		$service = Service::create($body);

		$result = [
			"service" => $service
		];

		$response = new JsonResponse($response);
		return $response->withJson($result);
	}

	public static function update(Request $request, Response $response, array $args) {
		$service = Service::getById($args['id']);

		if(!$service) {
			throw new ItemNotFoundException("service", "id: ".$args['id']);
		}

		$body = $request->getParsedBody();

		if(isset($body['name'])) {
			$service_with_name = Service::getByName($body['name']);

			if($service_with_name && $service->id !== $service_with_name->id) {
				throw new ItemAlreadyExistsException("service", "name: ".$body['name']);
			}
		}

		$updated_service = $service->update($body);

		$result = [
			"service" => $updated_service
		];

		$response = new JsonResponse($response);
		return $response->withJson($result);
	}

	public static function delete(Request $request, Response $response, array $args) {
		$service = Service::getById($args['id']);

		if(!$service) {
			throw new ItemNotFoundException("service", "id: ".$args['id']);
		}

		$service->delete();

		$result = [
			"service" => $service
		];

		$response = new JsonResponse($response);
		return $response->withJson($result);
	}
}