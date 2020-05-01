<?php


class Rental {
	public $id;
	public $name;
	public $description;
	public $physical_condition;
	public $notes;
	public $rate_id;
	public $user_id;

	public static function getAll() : array {
		$sql = "SELECT * FROM rentals";

		$db = Database::getInstance();
		$query = $db->prepare($sql);
		$query->execute();
		$result = $query->fetchAll(PDO::FETCH_CLASS, Rental::class);
		$query->closeCursor();

		return $result;
	}

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

	public static function getByName(string $name) : ?Rental {
		$sql = "SELECT * FROM rentals WHERE name = ?";

		$db = Database::getInstance();
		$query = $db->prepare($sql);
		$query->execute([$name]);
		$result = $query->fetchAll(PDO::FETCH_CLASS, Rental::class);
		$query->closeCursor();

		if(sizeof($result) === 0) {
			return null;
		}
		return $result[0];
	}

	public static function getAllByRateId(int $id) : array {
		$sql = "SELECT * FROM rentals WHERE rate_id = ?";

		$db = Database::getInstance();
		$query = $db->prepare($sql);
		$query->execute([$id]);
		$result = $query->fetchAll(PDO::FETCH_CLASS, Rate::class);
		$query->closeCursor();

		return $result;
	}

	public static function getAllByUserId(int $id) : array {
		$sql = "SELECT * FROM rentals WHERE user_id = ?";

		$db = Database::getInstance();
		$query = $db->prepare($sql);
		$query->execute([$id]);
		$result = $query->fetchAll(PDO::FETCH_CLASS, Rate::class);
		$query->closeCursor();

		return $result;
	}


	public static function create(array $data) : Rental {
		$sql = "
            INSERT INTO rentals (
            	name, 
            	description, 
            	physical_condition, 
            	notes,
			    rate_id,
			    user_id
			) VALUES (?,?,?,?,?)
        ";
		$args = [
			$data['name'],
			$data['description'] ?? null,
			$data['physical_condition'] ?? null,
			$data['notes'] ?? null,
			$data['rate_id'] ?? null,
			$data['user_id'] ?? null
		];

		$db = Database::getInstance();
		$query = $db->prepare($sql);
		$query->execute($args);
		$query->closeCursor();

		return Rental::getById($db->lastInsertId());
	}

	public function update(array $data) : Rental {

		$sql = "
            UPDATE rentals SET
            	name = ?,
            	description = ?,
            	physical_condition = ?,
            	notes = ?,
			    rate_id = ?,
			    user_id = ?
            WHERE 
                id = ?
        ";


		$args = [
			$data['name'] ?? $this->name,
			$data['description'] ?? $this->description,
			$data['physical_condition'] ?? $this->physical_condition,
			$data['notes'] ?? $this->notes,
			$data['rate_id'] ?? $this->rate_id,
			$data['user_id'] ?? $this->user_id,
			$this->id
		];

		$db = Database::getInstance();
		$query = $db->prepare($sql);
		$query->execute($args);

		$result = Rental::getById($this->id);

		return $result;
	}

	public function delete() : void {
		$sql = "DELETE FROM rentals WHERE id = ?";

		$db = Database::getInstance();
		$query = $db->prepare($sql);
		$query->execute([$this->id]);
		$query->closeCursor();
	}
}