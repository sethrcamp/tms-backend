<?php


class Rental {
	public $id;
	public $name;
	public $description;
	public $physical_condition;
	public $notes;

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

	public static function create(array $data) : Rental {
		$sql = "
            INSERT INTO rentals (
            	name, 
            	description, 
            	physical_condition, 
            	notes
			) VALUES (?,?,?,?)
        ";
		$args = [
			$data['name'],
			$data['description'] ?? null,
			$data['physical_condition'] ?? null,
			$data['notes'] ?? null
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
            	notes = ?
            WHERE 
                id = ?
        ";


		$args = [
			$data['name'] ?? $this->name,
			$data['description'] ?? $this->description,
			$data['physical_condition'] ?? $this->physical_condition,
			$data['notes'] ?? $this->notes,
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