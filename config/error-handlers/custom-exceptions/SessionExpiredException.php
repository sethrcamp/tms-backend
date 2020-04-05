<?php


class SessionExpiredException extends Exception {
	public function __construct(Throwable $previous = null) {
		parent::__construct("The current session has expired.", 401, $previous);
	}
}