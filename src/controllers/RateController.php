<?php

use Slim\Psr7\Request;
use Slim\Psr7\Response;

class RateController {

	public static function getAll(Request $request, Response $response, array $args) {
		$rates = Rate::getAll();
		$result = [
			"rates" => $rates
		];

		$response = new JsonResponse($response);
		return $response->withJson($result);
	}

	public static function getById(Request $request, Response $response, array $args) {
		$rate = Rate::getById($args['id']);

		if($rate === null) {
			throw new ItemNotFoundException("rate", "id: ".$args['id']);
		}

		$result = [
			"rate" => $rate
		];

		$response = new JsonResponse($response);
		return $response->withJson($result);
	}

	public static function getAllByType(Request $request, Response $response, array $args) {
		$type = $args['type'];

		if(!RateType::isValidName($type)) {
			throw new InvalidTypeException($type, "RateType");
		}

		$rates = Rate::getAllByType($type);
		$result = [
			"rates" => $rates
		];

		$response = new JsonResponse($response);
		return $response->withJson($result);
	}

	public static function getAllByServiceId(Request $request, Response $response, array $args) {
		$service = Service::getById($args['service_id']);

		if($service === null) {
			throw new ItemNotFoundException("service", "id: ".$args['service_id']);
		}

		$rates = Rate::getAllByServiceId($service->id);
		$result = [
			"rates" => $rates
		];

		$response = new JsonResponse($response);
		return $response->withJson($result);
	}

	public static function getAllByServiceIdAndType(Request $request, Response $response, array $args) {

		$service = Service::getById($args['service_id']);

		if($service === null) {
			throw new ItemNotFoundException("service", "id: ".$args['service_id']);
		}

		$type = $args['type'];

		if(!RateType::isValidName($type)) {
			throw new InvalidTypeException($type, "RateType");
		}

		$rates = Rate::getAllByServiceIdAndType($service->id, $type);
		$result = [
			"rates" => $rates
		];

		$response = new JsonResponse($response);
		return $response->withJson($result);
	}

	public static function getAllByServiceIdAndTypeAndTiming(Request $request, Response $response, array $args) {

		$service = Service::getById($args['service_id']);

		if($service === null) {
			throw new ItemNotFoundException("service", "id: ".$args['service_id']);
		}

		$type = $args['type'];

		if(!RateType::isValidName($type)) {
			throw new InvalidTypeException($type, "RateType");
		}

		$rates = Rate::getAllByServiceIdAndTypeAndTiming($service->id, $type, $args['timing']);
		$result = [
			"rates" => $rates
		];

		$response = new JsonResponse($response);
		return $response->withJson($result);
	}

	public static function create(Request $request, Response $response, array $args) {
		$body = $request->getParsedBody();

		$required_parameters = [
			'type',
			'timing',
			'service_id',
			'cost'
		];

		Helper::checkForAllParameters($body, $required_parameters);

		$type = $body['type'];

		if(!RateType::isValidName($type)) {
			throw new InvalidTypeException($type, "RateType");
		}

		if(!is_int($body['timing'])) {
			throw new InvalidTypeException("timing", "int");
		}

		$service = Service::getById($body['service_id']);

		if($service === null) {
			throw new ItemNotFoundException("service", "id: ".$body['service_id']);
		}

		if(!Helper::isDouble($body['cost'])) {
			throw new InvalidTypeException("cost", "double");
		}
		$body['cost'] = (float) $body['cost'];

		if($type === RateType::STANDARD) {
			$standard_rate = Rate::getAllByServiceIdAndTypeAndTimingAndCost($service->id, $type, $body['timing'],$body['cost']);

			if($standard_rate !== null) {
				throw new ItemAlreadyExistsException("STANDARD rate", "service_id: ".$service->id." timing: ".$body['timing']." and cost: ".$body['cost']);
			}
		}


		$rate = Rate::create($body);

		$result = [
			"rate" => $rate
		];

		$response = new JsonResponse($response);
		return $response->withJson($result);
	}

	public static function update(Request $request, Response $response, array $args) {
		$rate = Rate::getById($args['id']);

		if(!$rate) {
			throw new ItemNotFoundException("rate", "id: ".$args['id']);
		}

		$body = $request->getParsedBody();

		if(isset($body['type'])) {
			$type = $args['type'];

			if(!RateType::isValidName($type)) {
				throw new InvalidTypeException($type, "RateType");
			}
		}

		if(isset($body['timing']) && !is_int($body['timing'])) {
			throw new InvalidTypeException("timing", "int");
		}

		if(isset($body['service_id'])) {
			$service = Service::getById($args['service_id']);

			if($service === null) {
				throw new ItemNotFoundException("service", "id: ".$args['service_id']);
			}
		}

		if(isset($body['cost']) && !Helper::isDouble($body['cost'])) {
			throw new InvalidTypeException("cost", "double");
		}

		if(isset($type) && $type === RateType::STANDARD) {
			$service_id = isset($service) ? $service->id : $rate->service_id;
			$timing = $body['timing'] ?? $rate->timing;
			$cost = $body['cost'] ?? $rate->cost;

			$standard_rate = Rate::getAllByServiceIdAndTypeAndTiming($service_id, $type, $timing)[0];

			if($standard_rate !== null && $standard_rate->id !== $rate->id) {
				throw new ItemAlreadyExistsException("STANDARD rate", "service_id: ".$service_id." timing: ".$timing."and cost: ".$cost);
			}
		}

		$updated_rate = $rate->update($body);

		$result = [
			"rate" => $updated_rate
		];

		$response = new JsonResponse($response);
		return $response->withJson($result);
	}

	public static function delete(Request $request, Response $response, array $args) {
		$rate = Rate::getById($args['id']);

		if(!$rate) {
			throw new ItemNotFoundException("rate", "id: ".$args['id']);
		}

		$rate->delete();

		$result = [
			"term" => $rate
		];

		$response = new JsonResponse($response);
		return $response->withJson($result);
	}
}