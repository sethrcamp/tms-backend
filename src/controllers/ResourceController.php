<?php

use Slim\Psr7\Request;
use Slim\Psr7\Response;

class TMSResourceController {

	public static function getAll(Request $request, Response $response, array $args) {
		$resources = TMSResource::getAll();
		$result = [
			"resources" => $resources
		];

		$response = new JsonResponse($response);
		return $response->withJson($result);
	}

	public static function getById(Request $request, Response $response, array $args) {
		$resource = TMSResource::getById($args['id']);

		if($resource === null) {
			throw new ItemNotFoundException("resource", "id: ".$args['id']);
		}

		$result = [
			"resource" => $resource
		];

		$response = new JsonResponse($response);
		return $response->withJson($result);
	}

	public static function getAllByUserId(Request $request, Response $response, array $args) {
		$user = User::getById($args['user_id']);

		if(!$user) {
			throw new ItemNotFoundException("user", "id: ".$args['user_id']);
		}

		$resources = TMSResource::getAllByUser($user);

		$result = [
			"resources" => $resources
		];

		$response = new JsonResponse($response);
		return $response->withJson($result);
	}

	public static function getAllByType(Request $request, Response $response, array $args) {

		$type = $args['type'];

		if(!TMSResourceType::isValidName($type)) {
			throw new InvalidTypeException($type, 'TMSResourceType');
		}

		$resources = TMSResource::getAllByType($type);
		$result = [
			"resources" => $resources
		];

		$response = new JsonResponse($response);
		return $response->withJson($result);
	}

	public static function create(Request $request, Response $response, array $args) {
		$body = $request->getParsedBody();

		$required_parameters = [
			'content',
			'type'
		];

		Helper::checkForAllParameters($body, $required_parameters);

		if(!TMSResourceType::isValidName($body['type'])) {
			throw new InvalidTypeException($body['type'], 'TMSResourceType');
		}

		if(isset($body['user_id'])) {
			$user = User::getById($body['user_id']);

			if(!$user) {
				throw new ItemNotFoundException("user", "id: ".$body['user_id']);
			}
		}

		$resource = TMSResource::create($body);

		$result = [
			"resource" => $resource
		];

		$response = new JsonResponse($response);
		return $response->withJson($result);
	}

	public static function update(Request $request, Response $response, array $args) {
		$resource = TMSResource::getById($args['id']);

		if(!$resource) {
			throw new ItemNotFoundException("resource", "id: ".$args['id']);
		}

		$body = $request->getParsedBody();

		if(isset($body['user_id'])) {
			$user = User::getById($body['user_id']);

			if(!$user) {
				throw new ItemNotFoundException("user", "id: ".$body['user_id']);
			}
		}

		if(isset($body['type']) && !TMSResourceType::isValidName($body['type'])) {
			throw new InvalidTypeException($body['type'], 'TMSResourceType');
		}

		$updated_resource = $resource->update($body);

		$result = [
			"resource" => $updated_resource
		];

		$response = new JsonResponse($response);
		return $response->withJson($result);
	}

	public static function delete(Request $request, Response $response, array $args) {
		$resource = TMSResource::getById($args['id']);

		if (!$resource) {
			throw new ItemNotFoundException("resource", "id: ".$args['id']);
		}

		$resource->delete();

		$result = [
			"resource" => $resource
		];

		$response = new JsonResponse($response);
		return $response->withJson($result);
	}
}