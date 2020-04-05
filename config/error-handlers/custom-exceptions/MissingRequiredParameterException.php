<?php


class MissingRequiredParameterException extends Exception {

	public function __construct(string $parameter, Throwable $previous = null) {
		$message = "The request body is missing the required parameter: $parameter.";
		parent::__construct($message, 400, $previous);
	}

}