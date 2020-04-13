<?php


class Package {
	public $id;
	public $user_id;
	public $rate_id;
	public $rental_id;

	public static function getById(int $id) : ?Package {
		$sql = "SELECT * FROM packages WHERE id = ?";

		$db = Database::getInstance();
		$query = $db->prepare($sql);
		$query->execute([$id]);
		$result = $query->fetchAll(PDO::FETCH_CLASS, Package::class);
		$query->closeCursor();

		if(sizeof($result) === 0) {
			return null;
		}
		return $result[0];
	}
}