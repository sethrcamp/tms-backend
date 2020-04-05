<?php


class InvalidParameterException extends Exception {

	public function __construct(string $parameter, string $reason, Throwable $previous = null) {
		$message = "The parameter '$parameter' failed because $reason.";
		parent::__construct($message, 400, $previous);
	}

}