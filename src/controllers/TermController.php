<?php


use Slim\Psr7\Request;
use Slim\Psr7\Response;

class TermController {

	public static function getAll(Request $request, Response $response, array $args) {
		$terms = Term::getAll();
		$result = [
			"terms" => $terms
		];

		$response = new JsonResponse($response);
		return $response->withJson($result);
	}

	public static function getById(Request $request, Response $response, array $args) {
		$term = Term::getById($args['id']);

		if($term === null) {
			throw new ItemNotFoundException("term", "id: ".$args['id']);
		}

		$result = [
			"term" => $term
		];

		$response = new JsonResponse($response);
		return $response->withJson($result);
	}

	public static function getForDate(Request $request, Response $response, array $args) {

		$date = urldecode($args['date']);

		try {
			$date = new DateTime($date);
		} catch (Exception $e) {
			if($e->getCode() === 0) {
				throw new IncorrectTypeException($args['date'], "DateTime format");
			}
		}

		$term = Term::getForDate($date);

		if($term === null) {
			throw new ItemNotFoundException("term", "date: ".$args['date']);
		}

		$result = [
			"term" => $term
		];

		$response = new JsonResponse($response);
		return $response->withJson($result);
	}

	public static function getAllOpen(Request $request, Response $response, array $args) {
		
		$terms = Term::getAllOpen();

		$result = [
			"terms" => $terms
		];

		$response = new JsonResponse($response);
		return $response->withJson($result);
	}

	public static function create(Request $request, Response $response, array $args) {
		$body = $request->getParsedBody();

		$required_parameters = [
			'name',
			'start_date',
			'end_date'
		];

		Helper::checkForAllParameters($body, $required_parameters);

		$term_with_name = Term::getByName($body['name']);

		if($term_with_name) {
			throw new ItemAlreadyExistsException("term", "name: ".$body['name']);
		}

		$start_date = Helper::getDateObject($body['start_date']);
		$end_date = Helper::getDateObject($body['end_date']);

		if($start_date >= $end_date) {
			throw new InvalidParameterException("start_date and end_date", "the start_date cannot be on or after the end_date");
		}

		$body['start_date'] = $start_date;
		$body['end_date'] = $end_date;

		TermController::checkForOverlappingTerm($start_date, $end_date);

		$term = Term::create($body);

		$result = [
			"term" => $term
		];

		$response = new JsonResponse($response);
		return $response->withJson($result);
	}

	public static function update(Request $request, Response $response, array $args) {
		$term = Term::getById($args['id']);

		if(!$term) {
			throw new ItemNotFoundException("term", "id: ".$args['id']);
		}

		$body = $request->getParsedBody();

		if(isset($body['start_date']) || isset($body['end_date'])) {
			$start_date = Helper::getDateObject($body['start_date'] ?? $term->start_date);
			$end_date = Helper::getDateObject($body['end_date'] ?? $term->end_date);

			if($start_date >= $end_date) {
				throw new InvalidParameterException("start_date and end_date", "the start_date cannot be on or after the end_date");
			}

			$body['start_date'] = $start_date;
			$body['end_date'] = $end_date;

			TermController::checkForOverlappingTerm($start_date, $end_date, $term->id);
		}

		$term_with_name = Term::getByName($body['name']);

		if($term_with_name) {
			throw new ItemAlreadyExistsException("term", "name: ".$body['name']);
		}

		$updated_term = $term->update($body);

		$result = [
			"term" => $updated_term
		];

		$response = new JsonResponse($response);
		return $response->withJson($result);
	}

	public static function delete(Request $request, Response $response, array $args) {
		$term = Term::getById($args['id']);

		if(!$term) {
			throw new ItemNotFoundException("term", "id: ".$args['id']);
		}

		$term->delete();

		$result = [
			"term" => $term
		];

		$response = new JsonResponse($response);
		return $response->withJson($result);
	}

	private static function checkForOverlappingTerm(DateTime $start_date, DateTime $end_date, ?int $ignore_id = null) : void {
		$overlapping_terms = Term::getAllWithinRange($start_date, $end_date, $ignore_id);

		if(sizeof($overlapping_terms) > 0) {
			$dates_overlapped = "";
			foreach($overlapping_terms as $term) {
				$dates_overlapped .= $term->start_date." - ".$term->end_date.", ";
			}
			$dates_overlapped = substr($dates_overlapped, 0, -2);

			throw new ItemAlreadyExistsException("term(s)", "the date ranges: $dates_overlapped. These ranges overlap the given range of: ".$start_date->format(DB_DATE_FORMAT)." - ".$end_date->format(DB_DATE_FORMAT));
		}
	}
}