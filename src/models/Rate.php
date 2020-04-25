<?php


class RateType extends Enum {
	const STANDARD = "STANDARD";
	const DISCOUNT = "DISCOUNT";
	const CUSTOM = "CUSTOM";
}

class Rate {
	public $id;
	public $type;
	public $timing;
	public $service_id;
	public $cost;
}