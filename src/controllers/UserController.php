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

		if(!$user) {
			throw new ItemNotFoundException("user", "id: ".$args['id']);
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
			throw new InvalidTypeException($type, 'UserType');
		}

		$users = User::getAllByType($type);
		$result = [
			"users" => $users
		];

		$response = new JsonResponse($response);
		return $response->withJson($result);
	}
	
	public static function getByResetPasswordEmailId(Request $request, Response $response, array $args) {
		$user = User::getByResetPasswordEmailId($args['id']);

		if(!$user) {
			throw new ItemNotFoundException("user", "reset_password_email_id: ".$args['id']);
		}
		
		$reset_password_email = ResetPasswordEmail::getByUserId($user->id);
		
		if($reset_password_email->hasExpired()) {
			$reset_password_email->delete();
			throw new ResetPasswordEmailExpiredException($reset_password_email->id);
		}

		$result = [
			"user" => $user
		];

		$response = new JsonResponse($response);
		return $response->withJson($result);
	}

	public static function getAllByAgeClassification(Request $request, Response $response, array $args) {

		$age_classification = $args['age_classification'];

		if(!UserAgeClassification::isValidName($age_classification)) {
			throw new InvalidTypeException($age_classification, 'UserAgeClassification');
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
			throw new InvalidTypeException($body['age_classification'], 'UserAgeClassification');
		}

		if(isset($body['parent_id'])) {
			$parent = User::getById($body['parent_id']);

			if(!$parent) {
				throw new ItemNotFoundException("parent", "id: ".$body['parent_id']);
			}
		}

		if(isset($body['type']) && !UserType::isValidName($body['type'])) {
			throw new InvalidTypeException($body['type'], 'UserType');
		}

		$body['password'] = password_hash($body['password'], PASSWORD_BCRYPT);


		$user = User::create($body);

		$result = [
			"user" => $user
		];

		$response = new JsonResponse($response);
		return $response->withJson($result);
	}

	public static function sendResetPasswordEmail(Request $request, Response $response, array $args) {
		$body = $request->getParsedBody();

		$required_parameters = [
			'email'
		];

		Helper::checkForAllParameters($body, $required_parameters);

		$user_with_email = User::getByEmail($body['email']);

		if(!$user_with_email) {
			throw new ItemNotFoundException("user", "email: ".$body['email']);
		}

		$reset_password_email = ResetPasswordEmail::getByUserId($user_with_email->id);

		if($reset_password_email !== null && $reset_password_email->hasExpired()) {
			$reset_password_email->delete();
			$reset_password_email = null;
		}

		if($reset_password_email === null) {
			$reset_password_email = ResetPasswordEmail::create([
				"id" => Helper::generateToken(),
				"user_id" => $user_with_email->id
			]);
		}

		$html_body = "<p>A request to reset the password for twiggmusicstudio.com was received this for this email address. ".
			"If you did not make this request, you may ignore this email. ".
			"Click <a href='http://".RESET_PASSWORD_REDIRECT_URL.$reset_password_email->id."'>here</a> to reset your password.</p>";

		$email = new Email(
			DEFAULT_SENDER_EMAIL,
			$user_with_email->email,
			"Don't worry! We all forget sometimes!",
			$html_body
			);

		$email->sendMessage();

		$response = new JsonResponse($response);
		return $response->withJson(["status" => 200, "message" => "success"]);
	}

	public static function login(Request $request, Response $response, array $args) {
		$body = $request->getParsedBody();

		Helper::checkForAllParameters($body, ['email', 'password']);

		$user = User::getByEmail($body['email']);

		if(!$user) {
			throw new InvalidCredentialsException();
		}

		$user->verifyPassword($body['password']);

		$current_session = $user->getCurrentSession();

		if(!$current_session || $current_session->hasExpired()) {
			$current_session = Session::create($user);
		} else {
			$current_session = $current_session->extend();
		}

		$response = Helper::addSessionHeaders($response, $current_session);

		$response = new JsonResponse($response);

		if(isset($body['redirect_url'])) {
			return $response->withHeader('Location', $body['redirect_url']);
		}

		return $response->withJson(["user" => $user]);
	}

	public static function logout(Request $request, Response $response, array $args) {
		$user = User::getById($args['id']);

		if(!$user) {
			throw new ItemNotFoundException("post", "id: ".$args['id']);
		}

		$current_session = $user->getCurrentSession();
		$canceled_session = $current_session->cancel();

		$result = [
			"session" => $canceled_session
		];

		$response = new JsonResponse($response);
		return $response->withJson($result);
	}

	public static function logoutCurrent(Request $request, Response $response, array $args) {
		$user = $request->getAttribute("current_user");

		$current_session = $user->getCurrentSession();
		$canceled_session = $current_session->cancel();

		$result = [
			"session" => $canceled_session
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
			throw new InvalidTypeException($body['type'], 'UserType');
		}

		if(isset($body['age_classification']) && !UserAgeClassification::isValidName($body['age_classification'])) {
			throw new InvalidTypeException($body['age_classification'], 'UserAgeClassification');
		}

		if(isset($body['password'])) {
			$body['password'] = password_hash($body['password'], PASSWORD_BCRYPT);
		}

		if(isset($body['credit']) && !is_double($body['credit'])) {
			throw new InvalidTypeException($body['credit'], 'double');
		}

		$updated_user = $user->update($body);

		$result = [
			"user" => $updated_user
		];

		$response = new JsonResponse($response);
		return $response->withJson($result);
	}
	
	public static function resetPassword(Request $request, Response $response, array $args) {
		$user = User::getById($args['id']);

		if(!$user) {
			throw new ItemNotFoundException("user", "id: ".$args['id']);
		}

		$body = $request->getParsedBody();

		$required_parameters = [
			"reset_password_email_id",
			"password"
		];

		Helper::checkForAllParameters($body, $required_parameters);
		
		$reset_password_email = ResetPasswordEmail::getById($body['reset_password_email_id']);

		if(!$reset_password_email) {
			throw new ItemNotFoundException("reset_password_email", "id: ".$body['reset_password_email_id']);
		}
		
		if($reset_password_email->hasExpired()) {
			throw new ResetPasswordEmailExpiredException($reset_password_email->id);
		}
		
		if($user->id !== $reset_password_email->user_id) {
			throw new InvalidParameterException("reset_password_email_id", "there is no reset_password_email for a user with id: ".$args['id']);
		}

		$body['password'] = password_hash($body['password'], PASSWORD_BCRYPT);

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