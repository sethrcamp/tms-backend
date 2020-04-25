<?php


class ResetPasswordEmailExpiredException extends Exception {
	public function __construct(string $id, Throwable $previous = null) {
		parent::__construct("The reset_email_password with id: $id has expired.", 401, $previous);
	}
}