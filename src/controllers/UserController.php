<?php


use Slim\Psr7\Request;
use Slim\Psr7\Response;

class UserController {

	public static function getAll(Request $request, Response $response, array $args) {
		$users = User::getAll();
		$result = [
			"users" => $users
		];

		$response = new JsonResponse($response);
		return $response->withJson($result);
	}

	public static function getById(Request $request, Response $response, array $args) {
		$user = User::getById($args['id']);

		if($user === null) {
			throw new ItemNotFoundException("item", "id: ".$args['id']);
		}

		$result = [
			"user" => $user
		];

		$response = new JsonResponse($response);
		return $response->withJson($result);
	}

	public static function getAllByType(Request $request, Response $response, array $args) {

		$type = $args['type'];

		if(!UserType::isValidName($type)) {
			throw new IncorrectTypeException($type, 'UserType');
		}

		$users = User::getAllByType($type);
		$result = [
			"users" => $users
		];

		$response = new JsonResponse($response);
		return $response->withJson($result);
	}

	public static function getAllByAgeClassification(Request $request, Response $response, array $args) {

		$age_classification = $args['age_classification'];

		if(!UserAgeClassification::isValidName($age_classification)) {
			throw new IncorrectTypeException($age_classification, 'UserAgeClassification');
		}

		$users = User::getAllByAgeClassification($age_classification);
		$result = [
			"users" => $users
		];

		$response = new JsonResponse($response);
		return $response->withJson($result);
	}

	public static function create(Request $request, Response $response, array $args) {
		$body = $request->getParsedBody();

		$required_parameters = [
			'age_classification',
			'first_name',
			'last_name',
			'email',
			'password',
			'birthday'
		];

		Helper::checkForAllParameters($body, $required_parameters);


		$user_with_email = User::getByEmail($body['email']);

		if($user_with_email) {
			throw new ItemAlreadyExistsException("user", "email: ".$body['email']);
		}

		if(!UserAgeClassification::isValidName($body['age_classification'])) {
			throw new IncorrectTypeException($body['age_classification'], 'UserAgeClassification');
		}

		if(isset($body['parent_id'])) {
			$parent = User::getById($body['parent_id']);

			if(!$parent) {
				throw new ItemNotFoundException("parent", "id: ".$body['parent_id']);
			}
		}

		if(isset($body['type']) && !UserType::isValidName($body['type'])) {
			throw new IncorrectTypeException($body['type'], 'UserType');
		}

		$body['password'] = password_hash($body['password'], PASSWORD_BCRYPT);


		$user = User::create($body);

		$result = [
			"user" => $user
		];

		$response = new JsonResponse($response);
		return $response->withJson($result);
	}

	public static function update(Request $request, Response $response, array $args) {
		$user = User::getById($args['id']);

		if(!$user) {
			throw new ItemNotFoundException("user", "id: ".$args['id']);
		}

		$body = $request->getParsedBody();

		if(isset($body['parent_id'])) {
			$parent = User::getById($body['parent_id']);

			if(!$parent) {
				throw new ItemNotFoundException("parent", "id: ".$body['parent_id']);
			}

			if($parent->id === $user->id) {
				throw new InvalidParameterException('parent_id', 'a user cannot be their own parent');
			}
		}

		if(isset($body['type']) && !UserType::isValidName($body['type'])) {
			throw new IncorrectTypeException($body['type'], 'UserType');
		}

		if(isset($body['age_classification']) && !UserAgeClassification::isValidName($body['age_classification'])) {
			throw new IncorrectTypeException($body['age_classification'], 'UserAgeClassification');
		}

		if(isset($body['password'])) {
			$body['password'] = password_hash($body['password'], PASSWORD_BCRYPT);
		}

		if(isset($body['credit']) && !is_double($body['credit'])) {
			throw new IncorrectTypeException($body['credit'], 'double');
		}

		$updated_user = $user->update($body);

		$result = [
			"user" => $updated_user
		];

		$response = new JsonResponse($response);
		return $response->withJson($result);
	}

	public static function delete(Request $request, Response $response, array $args) {
		$user = User::getById($args['id']);



		if (!$user) {
			throw new ItemNotFoundException("user", "id: ".$args['id']);
		}

		$user->delete();

		$result = [
			"user" => $user
		];

		$response = new JsonResponse($response);
		return $response->withJson($result);
	}


}