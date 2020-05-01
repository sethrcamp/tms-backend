<?php


class Service {
	public $id;
	public $name;
	public $is_private;

	public static function getAll() : array {
		$sql = "SELECT * FROM services";

		$db = Database::getInstance();
		$query = $db->prepare($sql);
		$query->execute();
		$result = $query->fetchAll(PDO::FETCH_CLASS, Service::class);
		$query->closeCursor();

		return $result;
	}

	public static function getById(int $id) : ?Service {
		$sql = "SELECT * FROM services WHERE id = ?";

		$db = Database::getInstance();
		$query = $db->prepare($sql);
		$query->execute([$id]);
		$result = $query->fetchAll(PDO::FETCH_CLASS, Service::class);
		$query->closeCursor();

		if(sizeof($result) === 0) {
			return null;
		}
		return $result[0];
	}

	public static function getByName(string $name) : ?Service {
		$sql = "SELECT * FROM services WHERE name = ?";

		$db = Database::getInstance();
		$query = $db->prepare($sql);
		$query->execute([$name]);
		$result = $query->fetchAll(PDO::FETCH_CLASS, Service::class);
		$query->closeCursor();

		if(sizeof($result) === 0) {
			return null;
		}
		return $result[0];
	}

	public static function getAllByIsPrivate(int $is_private) : array {
		$sql = "SELECT * FROM services WHERE is_private = ?";

		$db = Database::getInstance();
		$query = $db->prepare($sql);
		$query->execute([$is_private]);
		$result = $query->fetchAll(PDO::FETCH_CLASS, Service::class);
		$query->closeCursor();

		return $result;
	}

	public static function create(array $data) : Service {
		$sql = "
            INSERT INTO services (
		    	name, 
			    is_private
			) VALUES (?,?)
        ";
		$args = [
			$data['name'],
			$data['is_private'] ?? 1
		];

		$db = Database::getInstance();
		$query = $db->prepare($sql);
		$query->execute($args);
		$query->closeCursor();

		return Service::getById($db->lastInsertId());
	}

	public function update(array $data) : Service {

		$sql = "
            UPDATE services
            SET name = ?,
            	is_private = ?
            WHERE 
                id = ?
        ";


		$args = [
			$data['name'] ?? $this->name,
			$data['is_private'] ?? $this->is_private,
			$this->id
		];

		$db = Database::getInstance();
		$query = $db->prepare($sql);
		$query->execute($args);

		$result = Service::getById($this->id);

		return $result;
	}

	public function delete() : void {
		$sql = "DELETE FROM services WHERE id = ?";

		$db = Database::getInstance();
		$query = $db->prepare($sql);
		$query->execute([$this->id]);
		$query->closeCursor();
	}
}