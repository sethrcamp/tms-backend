<?php


class ItemAlreadyExistsException extends Exception {
	public function __construct(string $type, string $reason = "the given data", Throwable $previous = null) {
		$message = "A $type already exists for $reason.";
		parent::__construct($message, 409, $previous);
	}
}