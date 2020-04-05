<?php


class InvalidCredentialsException extends Exception {
	public function __construct(Throwable $previous = null) {
		parent::__construct("Either the username or the password provided is incorrect.", 401, $previous);
	}
}