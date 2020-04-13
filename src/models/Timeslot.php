<?php


class Timeslot {
	public $id;
	public $start_time;
	public $end_time;
	public $availability_id;

	public static function getById(int $id) : ?Timeslot {
		$sql = "SELECT * FROM timeslots WHERE id = ?";

		$db = Database::getInstance();
		$query = $db->prepare($sql);
		$query->execute([$id]);
		$result = $query->fetchAll(PDO::FETCH_CLASS, Timeslot::class);
		$query->closeCursor();

		if(sizeof($result) === 0) {
			return null;
		}
		return $result[0];
	}

	public static function getAllByAvailabilityId(int $availability_id) : array {
		$sql = "SELECT * FROM timeslots WHERE availability_id = ? ORDER BY start_time";

		$db = Database::getInstance();
		$query = $db->prepare($sql);
		$query->execute([$availability_id]);
		$result = $query->fetchAll(PDO::FETCH_CLASS, Timeslot::class);
		$query->closeCursor();
		
		return $result;
	}

	public static function create($data) : Timeslot {
		$sql = "
        	INSERT INTO timeslots (
			    start_time, 
			    end_time, 
			    availability_id
			) VALUES (?,?,?)
        ";
		$args = [
			$data['start_time']->format(DB_TIME_FORMAT),
			$data['end_time']->format(DB_TIME_FORMAT),
			$data['availability']
		];

		$db = Database::getInstance();
		$query = $db->prepare($sql);
		$query->execute($args);
		$query->closeCursor();

		return Timeslot::getById($db->lastInsertId());
	}

	public function delete() : void {
		$sql = "DELETE FROM timeslots WHERE id = ?";

		$db = Database::getInstance();
		$query = $db->prepare($sql);
		$query->execute([$this->id]);
		$query->closeCursor();
	}
	
	public function isOpen() : bool {
		$sql = "SELECT * FROM timeslots_open WHERE id = ?";

		$db = Database::getInstance();
		$query = $db->prepare($sql);
		$query->execute([$this->id]);
		$result = $query->fetchAll(PDO::FETCH_CLASS, Timeslot::class);
		$query->closeCursor();

		return sizeof($result) > 0;
	}
}