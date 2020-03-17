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

	public static function getDateObject(string $date) : DateTime {
		try {
			$date = new DateTime($date);
			$date = new DateTime($date->format(DB_DATE_FORMAT));
		} catch (Exception $e) {
			if($e->getCode() === 0) {
				throw new IncorrectTypeException($body['start_date'], "DateTime format");
			}
		} finally {
			return $date;
		}
	}
}