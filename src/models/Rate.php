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

	public static function getAll() : array {
		$sql = "SELECT * FROM rates";

		$db = Database::getInstance();
		$query = $db->prepare($sql);
		$query->execute();
		$result = $query->fetchAll(PDO::FETCH_CLASS, Rate::class);
		$query->closeCursor();

		return $result;
	}

	public static function getById(int $id) : ?Rate {
		$sql = "SELECT * FROM rates WHERE id = ?";

		$db = Database::getInstance();
		$query = $db->prepare($sql);
		$query->execute([$id]);
		$result = $query->fetchAll(PDO::FETCH_CLASS, Rate::class);
		$query->closeCursor();

		if(sizeof($result) === 0) {
			return null;
		}
		return $result[0];
	}

	public static function getAllByType(string $type) : array {
		$sql = "SELECT * FROM rates WHERE type = ?";

		$db = Database::getInstance();
		$query = $db->prepare($sql);
		$query->execute([$type]);
		$result = $query->fetchAll(PDO::FETCH_CLASS, Rate::class);
		$query->closeCursor();

		return $result;
	}

	public static function getAllByServiceId(int $id) : array {
		$sql = "SELECT * FROM rates WHERE service_id = ?";

		$db = Database::getInstance();
		$query = $db->prepare($sql);
		$query->execute([$id]);
		$result = $query->fetchAll(PDO::FETCH_CLASS, Rate::class);
		$query->closeCursor();

		return $result;
	}

	public static function getAllByServiceIdAndType(int $id, string $type) : array {
		$sql = "SELECT * FROM rates WHERE service_id = ? AND type = ?";

		$db = Database::getInstance();
		$query = $db->prepare($sql);
		$query->execute([$id, $type]);
		$result = $query->fetchAll(PDO::FETCH_CLASS, Rate::class);
		$query->closeCursor();

		return $result;
	}

	public static function getAllByServiceIdAndTypeAndTiming(int $id, string $type, int $timing) : array {
		$sql = "SELECT * FROM rates WHERE service_id = ? AND type = ? AND timing = ?";

		$db = Database::getInstance();
		$query = $db->prepare($sql);
		$query->execute([$id, $type, $timing]);
		$result = $query->fetchAll(PDO::FETCH_CLASS, Rate::class);
		$query->closeCursor();

		return $result;
	}

	public static function getAllByServiceIdAndTypeAndTimingAndCost(int $id, string $type, int $timing, float $cost) : ?Rate {
		$sql = "SELECT * FROM rates WHERE service_id = ? AND type = ? AND timing = ? AND cost = ?";

		$db = Database::getInstance();
		$query = $db->prepare($sql);
		$query->execute([$id, $type, $timing, $cost]);
		$result = $query->fetchAll(PDO::FETCH_CLASS, Rate::class);
		$query->closeCursor();

		if(sizeof($result) === 0) {
			return null;
		}
		return $result[0];
	}

	public static function create(array $data) : Rate {
		$sql = "
            INSERT INTO rates (
		    	type, 
			    timing, 
			    service_id, 
			    cost
			) VALUES (?,?,?,?)
        ";
		$args = [
			$data['type'],
			$data['timing'],
			$data['service_id'],
			$data['cost']
		];

		$db = Database::getInstance();
		$query = $db->prepare($sql);
		$query->execute($args);
		$query->closeCursor();

		return Rate::getById($db->lastInsertId());
	}

	public function update(array $data) : Rate {

		$sql = "
            UPDATE rates
            SET 
                type = ?,
                timing = ?,
                service_id = ?,
                cost = ?
            WHERE 
                id = ?
        ";


		$args = [
			$data['type'] ?? $this->type,
			$data['timing'] ?? $this->timing,
			$data['service_id'] ?? $this->service_id,
			$data['cost'] ?? $this->cost,
			$this->id
		];

		$db = Database::getInstance();
		$query = $db->prepare($sql);
		$query->execute($args);

		$result = Rate::getById($this->id);

		return $result;
	}

	public function delete() : void {
		$sql = "DELETE FROM rates WHERE id = ?";

		$db = Database::getInstance();
		$query = $db->prepare($sql);
		$query->execute([$this->id]);
		$query->closeCursor();
	}
}