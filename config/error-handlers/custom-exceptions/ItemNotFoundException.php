<?php


class ItemNotFoundException extends Exception {

	public function __construct(string $type = "data",string $reason = "the given data", Throwable $previous = null) {
		$message = "No $type exists for $reason.";
		parent::__construct($message, 403, $previous);
	}

}