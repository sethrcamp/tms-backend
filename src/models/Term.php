<?php

class Term {

	public $id;
	public $start_date;
	public $end_date;
	public $name;

	public static function getAll() : array {
		$sql = "SELECT * FROM terms ORDER BY start_date";

		$db = Database::getInstance();
		$query = $db->prepare($sql);
		$query->execute();
		$result = $query->fetchAll(PDO::FETCH_CLASS, Term::class);
		$query->closeCursor();

		return $result;
	}

	public static function getById(int $id) : ?Term {
		$sql = "SELECT * FROM terms WHERE id = ?";

		$db = Database::getInstance();
		$query = $db->prepare($sql);
		$query->execute([$id]);
		$result = $query->fetchAll(PDO::FETCH_CLASS, Term::class);
		$query->closeCursor();

		if(sizeof($result) === 0) {
			return null;
		}
		return $result[0];
	}

	public static function getByName(string $name) : ?Term {
		$sql = "SELECT * FROM terms WHERE name = ?";

		$db = Database::getInstance();
		$query = $db->prepare($sql);
		$query->execute([$name]);
		$result = $query->fetchAll(PDO::FETCH_CLASS, Term::class);
		$query->closeCursor();

		if(sizeof($result) === 0) {
			return null;
		}
		return $result[0];
	}

	public static function getForDate(DateTime $date) : ?Term {
		$sql = "SELECT * FROM terms WHERE ? BETWEEN start_date AND end_date";

		$db = Database::getInstance();
		$query = $db->prepare($sql);
		$query->execute([$date->format(DB_DATE_FORMAT)]);
		$result = $query->fetchAll(PDO::FETCH_CLASS, Term::class);
		$query->closeCursor();

		if(sizeof($result) === 0) {
			return null;
		}
		return $result[0];
	}

	public static function getAllWithinRange(DateTime $start_date, DateTime $end_date, ?int $ignore_id = null) : array {
		$sql = "
			SELECT * FROM terms 
			WHERE id NOT IN (
			    SELECT id FROM terms 
			    WHERE start_date > ? 
			       OR end_date < ?
			) AND id != ?
		";

		$db = Database::getInstance();
		$query = $db->prepare($sql);
		$query->execute([$end_date->format(DB_DATE_FORMAT), $start_date->format(DB_DATE_FORMAT), $ignore_id ?? -1]);
		$result = $query->fetchAll(PDO::FETCH_CLASS, Term::class);
		$query->closeCursor();

		return $result;
	}

	public static function create(array $data) : Term {
		$sql = "
            INSERT INTO terms (
				start_date, 
				end_date, 
			    name
            ) VALUES (?,?,?)
        ";
		$args = [
			$data['start_date']->format(DB_DATE_FORMAT),
			$data['end_date']->format(DB_DATE_FORMAT),
			$data['name']
		];

		$db = Database::getInstance();
		$query = $db->prepare($sql);
		$query->execute($args);
		$query->closeCursor();

		return Term::getById($db->lastInsertId());
	}

	public function update(array $data) : Term {

		$sql = "
            UPDATE terms
            SET 
                start_date = ?,
                end_date = ?,
                name = ?
            WHERE 
                id = ?
        ";


		$args = [
			($data['start_date'] ?? $this->start_date)->format(DB_DATE_FORMAT),
			($data['end_date'] ?? $this->end_date)->format(DB_DATE_FORMAT),
			$data['name'] ?? $this->name,
			$this->id
		];

		$db = Database::getInstance();
		$query = $db->prepare($sql);
		$query->execute($args);

		$result = Term::getById($this->id);

		return $result;
	}

	public function delete() : void {
		$sql = "DELETE FROM terms WHERE id = ?";

		$db = Database::getInstance();
		$query = $db->prepare($sql);
		$query->execute([$this->id]);
		$query->closeCursor();
	}
}