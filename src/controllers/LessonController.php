<?php


use Slim\Psr7\Request;
use Slim\Psr7\Response;

class LessonController {
	public static function create(Request $request, Response $response, array $args) {
		$body = $request->getParsedBody();

		$required_parameters = [
			"student_id",
			"instructor_id",
			"package_id",
			"timeslot_id",
			"date"
		];

		Helper::checkForAllParameters($body, $required_parameters);

		LessonController::validateBody($body);

		//TODO: check if date is within day and term
		
		$lesson = Lesson::create($body);

		$result = [
			"lesson" => $lesson
		];

		$response = new JsonResponse($response);
		return $response->withJson($result);
	}
	
	public static function createForTerm(Request $request, Response $response, array $args) {
		$body = $request->getParsedBody();

		$required_parameters = [
			"student_id",
			"instructor_id",
			"package_id",
			"timeslot_id"
		];

		Helper::checkForAllParameters($body, $required_parameters);

		LessonController::validateBody($body);
		
		$timeslot = Timeslot::getById($body['timeslot_id']);
		$availability = Availability::getById($timeslot->availability_id);
		$term = Term::getById($availability->term_id);

		$now = new DateTime();
		$day = strtolower($availability->day);
		$current_lesson_date = new DateTime($term->start_date);

		if($now > $current_lesson_date) {
			$current_lesson_date = $now;
		}

		$last_day_of_term = new DateTime($term->end_date);
		
		$current_lesson_date->modify("next $day");


		$lessons = [];
		while($current_lesson_date <= $last_day_of_term) {
			$body['date'] = $current_lesson_date->format(DB_DATE_FORMAT);
			$lessons[] = Lesson::create($body);
			$current_lesson_date->modify("+1 week");
		}

		$result = [
			"lessons" => $lessons
		];

		$response = new JsonResponse($response);
		return $response->withJson($result);
	}
	
	private static function validateBody($body) {
		$required_parameters = [
			"student_id",
			"instructor_id",
			"package_id",
			"timeslot_id"
		];

		Helper::checkForAllParameters($body, $required_parameters);

		$student = User::getById($body['student_id']);

		if(!$student) {
			throw new ItemNotFoundException("user", "id: ".$body['student_id']);
		}

		if($student->type !== UserType::STUDENT) {
			throw new InvalidParameterException("student_id", "the user with id ".$body['student_id']." is not of type STUDENT");
		}

		$instructor = User::getById($body['instructor_id']);

		if(!$instructor) {
			throw new ItemNotFoundException("user", "id: ".$body['instructor_id']);
		}

		if($instructor->type !== UserType::ADMIN && $instructor->type !== UserType::SUPER_ADMIN) {
			throw new InvalidParameterException("student_id", "the user with id ".$body['instructor_id']." is not of type ADMIN or SUPER_ADMIN");
		}

		$package = Package::getById($body['package_id']);

		if(!$package) {
			throw new ItemNotFoundException("package", "id: ".$body['package_id']);
		}

		if($package->user_id !== $student->id) {
			throw new InvalidParameterException("package_id", "the package with id ".$package->id." is not for the user with id ".$student->id);
		}

		$timeslot = Timeslot::getById($body['timeslot_id']);

		if(!$timeslot) {
			throw new ItemNotFoundException("timeslot", "id: ".$body['timeslot_id']);
		}

		if(!$timeslot->isOpen()) {
			throw new InvalidParameterException("timeslot_id", "the timeslot with id ".$timeslot->id." already has a lesson scheduled");
		}
	}
	
}