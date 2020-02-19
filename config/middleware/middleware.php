<?php


use Slim\Psr7\Request;
use Slim\Psr7\Response;

class Validator {

	private $accepted_types;

	public function __construct($types = []) {
		foreach($types as $type) {
			if(!UserType::isValidName($type)) {
				throw new IncorrectTypeException($type, "UserType");
			}
		}
		$this->accepted_types = $types;
	}

	public function __invoke(Request $request, Response $response, $next) {
		if(!REQUIRE_VALIDATION) {
			return $next($request, $response);
		}

		if(defined("DEVELOPMENT_USER_ID") && DEVELOPMENT_USER_ID !== null) {
			$current_user = User::getById(DEVELOPMENT_USER_ID);
			$request = $request->withAttribute('current_user', $current_user);
			return $next($request, $response);
		}

		$session_key = $_COOKIE["session_key"] ?? null;
		$session_id =  $_COOKIE["session_id"]  ?? null;

		if($session_key === null) {
			throw new MissingRequiredCredentialsException("session_key");
		}

		if($session_id === null) {
			throw new MissingRequiredCredentialsException("session_id");
		}

		$session = Session::getByIdAndKey($session_id, $session_key);

		if($session === null) {
			throw new ItemNotFoundException("session", "id: ".$session_id." and key: ".$session_key);
		}

		$current_user = $session->getUser();

		if($session->hasExpired()) {
			if(AUTOMATICALLY_REGENERATE_SESSIONS) {
				$session = Session::create($current_user);
				$response = Helper::addSessionHeaders($response, $session);
			} else {
				throw new SessionExpiredException();
			}
		} else {
			$session->extend();
		}

		$user_is_of_valid_type = sizeof($this->accepted_types) === 0;
		foreach($this->accepted_types as $type) {
			if($current_user->type === $type) {
				$user_is_of_valid_type = true;
				break;
			}
		}

		if(!$user_is_of_valid_type) {
			throw new InvalidPermissionsException();
		}

		$request = $request->withAttribute('current_user', $current_user);

		return $next($request, $response);
	}
}