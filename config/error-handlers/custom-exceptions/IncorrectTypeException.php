<?php


class IncorrectTypeException extends Exception {
	public function __construct(string $given_type, string $expected_type, Throwable $previous = null) {
		$message = "$given_type is not a valid $expected_type.";
		parent::__construct($message, 403, $previous);
	}
}