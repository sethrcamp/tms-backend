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

		$lesson = Lesson::create($body);

		$result = [
			"lesson" => $lesson
		];

		$response = new JsonResponse($response);
		return $response->withJson($result);
	}
}