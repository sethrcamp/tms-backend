<?php

use Slim\Psr7\Request;
use Slim\Psr7\Response;

class TransactionController {

	public static function getAll(Request $request, Response $response, array $args) {
		$transactions = Transaction::getAll();
		$result = [
			"transactions" => $transactions
		];

		$response = new JsonResponse($response);
		return $response->withJson($result);
	}

	public static function getById(Request $request, Response $response, array $args) {
		$transaction = Transaction::getById($args['id']);

		if($transaction === null) {
			throw new ItemNotFoundException("transaction", "id: ".$args['id']);
		}
		
		$result = [
			"transaction" => $transaction
		];

		$response = new JsonResponse($response);
		return $response->withJson($result);
	}

	public static function create(Request $request, Response $response, array $args) {
		$body = $request->getParsedBody();

		$required_parameters = [
			'to_user_id',
			'amount',
			'type',
			'description'
		];

		Helper::checkForAllParameters($body, $required_parameters);

		if(!TransactionType::isValidName($body['type'])) {
			throw new InvalidTypeException($body['type'], "TransactionType");
		}

		if(isset($body['from_user_id'])) {
			$from_user = User::getById($body['from_user_id']);

			if($from_user === null) {
				throw new ItemNotFoundException("user", "id: ".$body['from_user_id']);
			}

			if($body['type'] === TransactionType::PAYMENT) {
				if($from_user->type !== UserType::STUDENT) {
					throw new InvalidParameterException("type", "the from_user for a PAYMENT must be of type STUDENT");
				}
			} else if($from_user->type === UserType::STUDENT) {
				throw new InvalidParameterException("type", "the from_user for a CHARGE or CREDIT must be of type ADMIN or SUPER_ADMIN");
			}
		} else {
			$super_admin = User::getSuperAdmin();
			$body['from_user_id'] = $super_admin->id;
		}

		$to_user = User::getById($body['to_user_id']);

		if($to_user === null) {
			throw new ItemNotFoundException("user", "id: ".$body['to_user_id']);
		}

		if($body['type'] === TransactionType::PAYMENT) {
			if($to_user->type === UserType::STUDENT) {
				throw new InvalidParameterException("type", "the to_user for a PAYMENT must be of type ADMIN or SUPER_ADMIN");
			}
		} else if($to_user->type !== UserType::STUDENT) {
			throw new InvalidParameterException("type", "the to_user for a CHARGE or CREDIT must be of type STUDENT");
		}

		if(!Helper::isDouble($body['amount'])) {//ensures the value is a valid PHP float, then checks if it has 0,1, or 2 decimal places
			throw new InvalidParameterException("amount", "amount must be a double.");
		}

		if(!TransactionType::isValidName($body['type'])) {
			throw new InvalidTypeException($body['type'], "TransactionType");
		}

		if(isset($body['lesson_id']) && isset($body['rental_id'])) {
			throw new InvalidParameterException("lesson_id and rental_id", "a transaction cannot be tied to both a lesson and a rental");
		}

		if(isset($body['lesson_id'])) {
			$lesson = Lesson::getById($body['lesson_id']);

			if($lesson === null) {
				throw new ItemNotFoundException("lesson", "id: ".$body['lesson_id']);
			}
		}

		if(isset($body['rental_id'])) {
			$rental = Rental::getById($body['rental_id']);

			if($rental === null) {
				throw new ItemNotFoundException("rental", "id: ".$body['rental_id']);
			}
		}

		$transaction = Transaction::create($body);

		$result = [
			"transaction" => $transaction
		];

		$response = new JsonResponse($response);
		return $response->withJson($result);
	}

}