<?php

class TransactionType extends Enum {
	const PAYMENT = "PAYMENT";
	const BILLING = "BILLING";
	const TRANSFER = "TRANSFER";
}

class Transaction {
	public $id;
	public $user_id;
	public $amount;
	public $type;
	public $created_time;
	public $description;
}