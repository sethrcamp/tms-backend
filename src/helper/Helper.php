<?php


use Slim\Psr7\Response;

class Helper {
	public static function checkForAllParameters(array $body, array $requiredParams) : void {
		foreach ($requiredParams as $param) {
			if (!isset($body[$param])) {
				throw new MissingRequiredParameterException($param);
			}
		}
	}

	public static function generateToken() : string {
		return bin2hex(openssl_random_pseudo_bytes(50));
	}

	public static function addSessionHeaders(Response $response, Session $session) {
		$expiration_date = date("D, j M Y H:i:s e", time()+(SESSION_LENGTH_IN_MINUTES*60));
		$response = $response->withHeader("Set-Cookie", "session_key=".$session->session_key."; Expires=".$expiration_date."; Path=/; SameSite=Strict; httpOnly");
		$response = $response->withAddedHeader("Set-Cookie", "session_id=".$session->id."; Expires=".$expiration_date."; Path=/; SameSite=Strict; httpOnly");
		return $response;
	}
	
	public static function isDouble(float $float) : bool {
		$float = (string) $float;
		$exploded = explode(".", $float);
		$decimal_count = strlen($exploded[1]);
		return $decimal_count <= 2;
	}

	public static function getDateObject(string $date) : DateTime {
		return Helper::getFormattedDateTimeObject($date, DB_DATE_FORMAT);
	}

	public static function getTimeObject(string $time) : DateTime {
		return Helper::getFormattedDateTimeObject($time, DB_TIME_FORMAT);
	}

	public static function getDateTimeObject(string $date_time) : DateTime {
		return Helper::getFormattedDateTimeObject($date_time, DB_DATETIME_FORMAT);
	}

	private static function getFormattedDateTimeObject(string $date_time, string $DB_FORMAT) : DateTime {
		try {
			$date_time_formatted = new DateTime($date_time);
			$date_time_formatted = new DateTime($date_time_formatted->format($DB_FORMAT));
		} catch (Exception $e) {
			if($e->getCode() === 0) {
				throw new IncorrectTypeException($date_time, "DateTime format");
			}
		}

		return $date_time_formatted;
	}
	
	
}