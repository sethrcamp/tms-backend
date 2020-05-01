<?php

use Slim\Psr7\Request;
use Slim\Psr7\Response;

class HomeworkResourceController {

	public static function getAll(Request $request, Response $response, array $args) {
		$homework_resources = HomeworkResource::getAll();
		$result = [
			"homework_resources" => $homework_resources
		];

		$response = new JsonResponse($response);
		return $response->withJson($result);
	}

	public static function getById(Request $request, Response $response, array $args) {
		$homework_resource = HomeworkResource::getById($args['id']);

		if($homework_resource === null) {
			throw new ItemNotFoundException("homework_resource", "id: ".$args['id']);
		}

		$result = [
			"homework_resource" => $homework_resource
		];

		$response = new JsonResponse($response);
		return $response->withJson($result);
	}

	public static function create(Request $request, Response $response, array $args) {
		$body = $request->getParsedBody();

		$required_parameters = [
			'lesson_id',
			'url'
		];

		Helper::checkForAllParameters($body, $required_parameters);

		$lesson = Lesson::getById($body['lesson_id']);

		if($lesson === null) {
			throw new ItemNotFoundException("lesson", "id: ".$body['lesson_id']);
		}

		if(!filter_var($body['url'], FILTER_VALIDATE_URL)) {
			throw new InvalidTypeException("url", "url");
		}

		$homework_resource = HomeworkResource::create($body);

		$result = [
			"homework_resource" => $homework_resource
		];

		$response = new JsonResponse($response);
		return $response->withJson($result);
	}


	public static function getAllByLessonId(Request $request, Response $response, array $args) {

		$homework_resources = HomeworkResource::getAllByLessonId($args['id']);

		$result = [
			"homework_resources" => $homework_resources
		];

		$response = new JsonResponse($response);
		return $response->withJson($result);
	}

	public static function update(Request $request, Response $response, array $args) {
		$homework_resource = HomeworkResource::getById($args['id']);

		if(!$homework_resource) {
			throw new ItemNotFoundException("homework_resource", "id: ".$args['id']);
		}

		$body = $request->getParsedBody();

		$required_parameters = [
			'url'
		];

		Helper::checkForAllParameters($body, $required_parameters);

		if(!filter_var($body['url'], FILTER_VALIDATE_URL)) {
			throw new InvalidTypeException("url", "url");
		}

		$updated_homework_resource = $homework_resource->update($body);

		$result = [
			"homework_resource" => $updated_homework_resource
		];

		$response = new JsonResponse($response);
		return $response->withJson($result);
	}

	public static function delete(Request $request, Response $response, array $args) {
		$homework_resources = HomeworkResource::getById($args['id']);

		if(!$homework_resources) {
			throw new ItemNotFoundException("homework_resources", "id: ".$args['id']);
		}

		$homework_resources->delete();

		$result = [
			"homework_resources" => $homework_resources
		];

		$response = new JsonResponse($response);
		return $response->withJson($result);
	}
}