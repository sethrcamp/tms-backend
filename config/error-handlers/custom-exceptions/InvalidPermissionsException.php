<?php


class InvalidPermissionsException extends Exception {
	public function __construct(Throwable $previous = null) {
		parent::__construct("You do not have permission to call this endpoint.", 401, $previous);
	}
}