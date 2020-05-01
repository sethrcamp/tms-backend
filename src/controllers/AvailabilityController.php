<?php


use Slim\Psr7\Request;
use Slim\Psr7\Response;

class AvailabilityController {
	public static function getAll(Request $request, Response $response, array $args) {
		$availabilities = Availability::getAll();
		$result = [
			"availabilities" => $availabilities
		];

		$response = new JsonResponse($response);
		return $response->withJson($result);
	}

	public static function getById(Request $request, Response $response, array $args) {
		$availability = Availability::getById($args['id']);

		if($availability === null) {
			throw new ItemNotFoundException("availability", "id: ".$args['id']);
		}

		$result = [
			"availability" => $availability
		];

		$response = new JsonResponse($response);
		return $response->withJson($result);
	}
	
	public static function getAllOpenTimeslotsByTermId(Request $request, Response $response, array $args) {
		$term = Term::getById($args['term_id']);

		if(!$term) {
			throw new ItemNotFoundException("term", "id: ".$args['term_id']);
		}
		
		$timeslots = Timeslot::getAllOpenByTermId($term->id);
		
		$days = [];
		
		foreach($timeslots as $timeslot) {
			$day = $timeslot['day'];
			unset($timeslot['day']);
			
			if(!isset($days[$day])) {
				$days[$day] = [
					"day" => $day,
					"open_slots" => []
				];
			}

			$days[$day]["open_slots"][] = $timeslot;
		}

		$result = [
			"days" => array_values($days)
		];

		$response = new JsonResponse($response);
		return $response->withJson($result);
	}

	public static function create(Request $request, Response $response, array $args) {
		$body = $request->getParsedBody();

		$required_parameters = [
			"instructor_id",
			"term_id",
			"day",
			"start_time",
			"end_time"
		];

		Helper::checkForAllParameters($body, $required_parameters);

		$instructor = User::getById($body['instructor_id']);

		if(!$instructor) {
			throw new ItemNotFoundException("user", "id: ".$body['instructor_id']);
		}

		$term = Term::getById($body['term_id']);

		if(!$term) {
			throw new ItemNotFoundException("term", "id: ".$body['term_id']);
		}

		if(!DayOfWeek::isValidName($body['day'])) {
			throw new InvalidTypeException($body['day'], 'DayOfWeek');
		}

		$start_time = Helper::getTimeObject($body['start_time']);
		$end_time = Helper::getTimeObject($body['end_time']);

		if($start_time >= $end_time) {
			throw new InvalidParameterException("start_time and end_time", "the start_time cannot be on or after the end_time");
		}

		$body['start_time'] = $start_time;
		$body['end_time'] = $end_time;

		AvailabilityController::checkForOverlappingAvailabilities($start_time, $end_time, $body['day'], $instructor->id, $term->id);

		$time_increment = (int) ($body['time_increment'] ?? 30);

		if((($end_time->getTimestamp() - $start_time->getTimestamp()) / 60) % $time_increment !== 0) {
			throw new InvalidParameterException("time_increment" , "the time_increment must evenly divide the total availability time");
		}

		$availability = Availability::create($body);
		
		$timeslots = $availability->generateNewTimeslots();

		$result = [
			"availability" => $availability,
			"timeslots" => $timeslots
		];

		$response = new JsonResponse($response);
		return $response->withJson($result);
	}

	public static function update(Request $request, Response $response, array $args) {
		$availability = Availability::getById($args['id']);

		if(!$availability) {
			throw new ItemNotFoundException("availability", "id: ".$args['id']);
		}

		$body = $request->getParsedBody();

		if(isset($body['term'])) {
			$term = Term::getById($body['term_id']);

			if(!$term) {
				throw new ItemNotFoundException("term", "id: ".$body['term_id']);
			}
		}

		if(isset($body['day']) && !DayOfWeek::isValidName($body['day'])) {
			throw new InvalidTypeException($body['day'], 'DayOfWeek');
		}

		$start_time = Helper::getTimeObject($body['start_time'] ?? $availability->start_time);
		$end_time = Helper::getTimeObject($body['end_time'] ?? $availability->end_time);

		if(isset($body['start_time']) || isset($body['end_time'])) {
			if($start_time >= $end_time) {
				throw new InvalidParameterException("start_time and end_time", "the start_time cannot be on or after the end_time");
			}
		}

		if(isset($body['start_time']) || isset($body['end_time']) || isset($body['day'])) {
			AvailabilityController::checkForOverlappingAvailabilities($start_time, $end_time, $body['day'] ?? $availability->day, $availability->instructor_id, $body['term_id'] ?? $availability->term_id, $availability->id);
		}

		$do_timeslot_generation = false;
		if(isset($body['start_time']) || isset($body['end_time']) || isset($body['time_increment'])) {
			$time_increment = (int) ($body['time_increment'] ?? $availability->time_increment);

			if((($end_time->getTimestamp() - $start_time->getTimestamp()) / 60) % $time_increment !== 0) {
				throw new InvalidParameterException("time_increment" , "the time_increment must evenly divide the total availability time");
			}

			$do_timeslot_generation = true;
		}

		$body['start_time'] = $start_time;
		$body['end_time'] = $end_time;

		$updated_availability = $availability->update($body);

		$timeslots = $updated_availability->getTimeslots();

		if($do_timeslot_generation) {
			$timeslots = $updated_availability->generateNewTimeslots();
		}

		$result = [
			"availability" => $updated_availability,
			"timeslots" => $timeslots
		];

		$response = new JsonResponse($response);
		return $response->withJson($result);
	}



	private static function checkForOverlappingAvailabilities(DateTime $start_time, DateTime $end_time, string $day, int $instructor_id, int $term_id, ?int $ignore_id = null) : void {
		$overlapping_availabilities = Availability::getAllWithinRange($start_time, $end_time, $day, $instructor_id, $term_id, $ignore_id);

		if(sizeof($overlapping_availabilities) > 0) {
			$availabilities_overlapped = "";
			foreach($overlapping_availabilities as $availability) {
				$availabilities_overlapped .= $availability->day."'s ".$availability->start_time." - ".$availability->end_time.", ";
			}
			$availabilities_overlapped = substr($availabilities_overlapped, 0, -2);

			throw new ItemAlreadyExistsException("availability(ies)", "the date ranges: $availabilities_overlapped. These ranges overlap the given range of: ".$day."'s ".$start_time->format(DB_TIME_FORMAT)." - ".$end_time->format(DB_TIME_FORMAT));
		}
	}

	public static function delete(Request $request, Response $response, array $args) {
		$availability = Availability::getById($args['id']);

		if(!$availability) {
			throw new ItemNotFoundException("availability", "id: ".$args['id']);
		}

		$availability->delete();

		$result = [
			"availability" => $availability
		];

		$response = new JsonResponse($response);
		return $response->withJson($result);
	}

}