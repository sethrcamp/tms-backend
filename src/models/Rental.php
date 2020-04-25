<?php


class Rental {
	public $id;
	public $name;
	public $description;
	public $condition;
	public $notes;

	public static function getById(int $id) : ?Rental {
		$sql = "SELECT * FROM rentals WHERE id = ?";

		$db = Database::getInstance();
		$query = $db->prepare($sql);
		$query->execute([$id]);
		$result = $query->fetchAll(PDO::FETCH_CLASS, Rental::class);
		$query->closeCursor();

		if(sizeof($result) === 0) {
			return null;
		}
		return $result[0];
	}
}