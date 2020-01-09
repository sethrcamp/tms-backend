<?php


use Slim\Psr7\Response;

class Helper {
	public static function checkForAllParameters($body, $requiredParams) {
		foreach ($requiredParams as $param) {
			if (!isset($body[$param])) {
				throw new MissingRequiredParameterException($param);
			}
		}
	}
}