<?php


class MissingRequiredCredentialsException extends Exception {

	public function __construct(string $parameter, Throwable $previous = null) {
		$message = "The cookie for $parameter is not set.";
		parent::__construct($message, 401, $previous);
	}

}