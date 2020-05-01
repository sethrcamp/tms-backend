<?php

use Slim\Psr7\Request;
use Slim\Psr7\Response;

class RentalController {

	public static function getAll(Request $request, Response $response, array $args) {
		$rentals = Rental::getAll();
		$result = [
			"rentals" => $rentals
		];

		$response = new JsonResponse($response);
		return $response->withJson($result);
	}

	public static function getById(Request $request, Response $response, array $args) {
		$rental = Rental::getById($args['id']);

		if($rental === null) {
			throw new ItemNotFoundException("rental", "id: ".$args['id']);
		}

		$result = [
			"rental" => $rental
		];

		$response = new JsonResponse($response);
		return $response->withJson($result);
	}

	public static function getByAllRateId(Request $request, Response $response, array $args) {

		$rate = Rate::getById($args['rate_id']);

		if($rate === null) {
			throw new ItemNotFoundException("rate", "id: ".$args['rate_id']);
		}

		$rentals = Rental::getAllByRateId($rate->id);

		$result = [
			"rentals" => $rentals
		];

		$response = new JsonResponse($response);
		return $response->withJson($result);
	}

	public static function getByAllUserId(Request $request, Response $response, array $args) {

		$user = Rate::getById($args['user_id']);

		if($user === null) {
			throw new ItemNotFoundException("user", "id: ".$args['user_id']);
		}

		$rentals = Rental::getAllByUserId($user->id);

		$result = [
			"rentals" => $rentals
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

		$rental_with_name = Rental::getByName($body['name']);

		if($rental_with_name) {
			throw new ItemAlreadyExistsException("rental", "name: ".$body['name']);
		}

		if(isset($body['rate_id'])) {
			$rate = Rate::getById($body['rate_id']);

			if($rate === null) {
				throw new ItemNotFoundException("rate", "id: ".$body['rate_id']);
			}
		}

		if(isset($body['user_id'])) {
			$user = User::getById($body['user_id']);

			if($user === null) {
				throw new ItemNotFoundException("user", "id: ".$body['user_id']);
			}
		}

		$rental = Rental::create($body);

		$result = [
			"rental" => $rental
		];

		$response = new JsonResponse($response);
		return $response->withJson($result);
	}

	public static function update(Request $request, Response $response, array $args) {
		$rental = Rental::getById($args['id']);

		if(!$rental) {
			throw new ItemNotFoundException("rental", "id: ".$args['id']);
		}

		$body = $request->getParsedBody();

		if(isset($body['name'])) {
			$rental_with_name = Rental::getByName($body['name']);

			if($rental_with_name && $rental_with_name->id !== $rental->id) {
				throw new ItemAlreadyExistsException("rental", "name: ".$body['name']);
			}
		}

		if(isset($body['rate_id'])) {
			$rate = rate::getById($body['rate_id']);

			if($rate === null) {
				throw new ItemNotFoundException("rate", "id: ".$body['rate_id']);
			}

		}

		if(isset($body['user_id'])) {
			$user = User::getById($body['user_id']);

			if($user === null) {
				throw new ItemNotFoundException("user", "id: ".$body['user_id']);
			}
		}

		$updated_rental = $rental->update($body);

		$result = [
			"rental" => $updated_rental
		];

		$response = new JsonResponse($response);
		return $response->withJson($result);
	}

	public static function delete(Request $request, Response $response, array $args) {
		$rental = Rental::getById($args['id']);

		if(!$rental) {
			throw new ItemNotFoundException("rental", "id: ".$args['id']);
		}

		$rental->delete();

		$result = [
			"rental" => $rental
		];

		$response = new JsonResponse($response);
		return $response->withJson($result);
	}
}