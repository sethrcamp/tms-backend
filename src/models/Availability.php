<?php

abstract class DayOfWeek extends Enum {
	const SUNDAY = "SUNDAY";
	const MONDAY = "MONDAY";
	const TUESDAY = "TUESDAY";
	const WEDNESDAY = "WEDNESDAY";
	const THURSDAY = "THURSDAY";
	const FRIDAY = "FRIDAY";
	const SATURDAY = "SATURDAY";
}

class Availability {

	public $id;
	public $instructor_id;
	public $day;
	public $start_time;
	public $end_time;
	public $time_increment;
	public $term_id;

	public static function getAll() : array {
		$sql = "
			SELECT availabilities.*
			FROM availabilities JOIN terms 
			    ON availabilities.term_id = terms.id 
			ORDER BY terms.start_date, availabilities.day, availabilities.start_time
		";

		$db = Database::getInstance();
		$query = $db->prepare($sql);
		$query->execute();
		$result = $query->fetchAll(PDO::FETCH_CLASS, Availability::class);
		$query->closeCursor();

		return $result;
	}

	public static function getById(int $id) : ?Availability {
		$sql = "SELECT * FROM availabilities WHERE id = ?";

		$db = Database::getInstance();
		$query = $db->prepare($sql);
		$query->execute([$id]);
		$result = $query->fetchAll(PDO::FETCH_CLASS, Availability::class);
		$query->closeCursor();

		if(sizeof($result) === 0) {
			return null;
		}
		return $result[0];
	}

	public static function getAllOpenByTermId(int $id) : array {
		$sql = "
			SELECT availabilities.id, COUNT(*) as total_timeslots_available
			FROM availabilities 
			JOIN timeslots
				ON availabilities.id = timeslots.availability_id
			GROUP BY timeslots.availability_id
		";

		$db = Database::getInstance();
		$query = $db->prepare($sql);
		$query->execute([$id]);
		$result = $query->fetchAll(PDO::FETCH_CLASS, Availability::class);
		$query->closeCursor();

		return $result;
	}

	public static function getAllWithinRange(DateTime $start_date, DateTime $end_date, string $day, int $instructor_id, int $term_id, ?int $ignore_id = null) : array {
		$sql = "
			SELECT * FROM availabilities 
			WHERE id NOT IN (
			    SELECT id FROM availabilities 
			    WHERE (
			        start_time > ? 
			        OR 
			        end_time < ?
				) 
			) 
			AND day = ?
			AND instructor_id = ?  
			AND term_id = ?
		    AND id != ?  
		";

		$db = Database::getInstance();
		$query = $db->prepare($sql);
		$query->execute([
			$end_date->format(DB_TIME_FORMAT),
			$start_date->format(DB_TIME_FORMAT),
			$day,
			$instructor_id,
			$term_id,
			$ignore_id ?? -1
		]);
		$result = $query->fetchAll(PDO::FETCH_CLASS, Availability::class);
		$query->closeCursor();


		return $result;
	}

	public static function create($data) : Availability {
		$sql = "
            INSERT INTO availabilities (
				instructor_id, 
				term_id, 
				day, 
				start_time, 
				end_time, 
				time_increment
			) VALUES (?,?,?,?,?,?)
        ";
		$args = [
			$data['instructor_id'],
			$data['term_id'],
			$data['day'],
			$data['start_time']->format(DB_DATETIME_FORMAT),
			$data['end_time']->format(DB_DATETIME_FORMAT),
			$data['time_increment'] ?? 30
		];

		$db = Database::getInstance();
		$query = $db->prepare($sql);
		$query->execute($args);
		$query->closeCursor();

		return Availability::getById($db->lastInsertId());
	}

	public function update(array $data) : Availability {

		$sql = "
            UPDATE availabilities
            SET day = ?,
				start_time = ?,
				end_time = ?,
				time_increment = ?,
                term_id = ?
            WHERE 
                id = ?
        ";


		$args = [
			$data['day'] ?? $this->day,
			($data['start_time'] ?? $this->start_time)->format(DB_TIME_FORMAT),
			($data['end_time'] ?? $this->end_time)->format(DB_TIME_FORMAT),
			$data['time_increment'] ?? $this->time_increment,
			$data['term_id'] ?? $this->term_id,
			$this->id
		];

		$db = Database::getInstance();
		$query = $db->prepare($sql);
		$query->execute($args);

		$result = Availability::getById($this->id);

		return $result;
	}

	public function delete() : void {
		$sql = "DELETE FROM availabilities WHERE id = ?";

		$db = Database::getInstance();
		$query = $db->prepare($sql);
		$query->execute([$this->id]);
		$query->closeCursor();
	}

	public function getTimeslots() {
		return Timeslot::getAllByAvailabilityId($this->id);
	}

	public function generateNewTimeslots() {
		$original_timeslots = $this->getTimeslots();
		foreach($original_timeslots as $timeslot) {
			$timeslot->delete();
		}

		$timeslots = [];

		$start_time = new DateTime($this->start_time);
		$end_time = new DateTime($this->end_time);

		do {
			$timeslot_start_time = $timeslot_end_time ?? $start_time;
			$timeslot_end_time = (clone $timeslot_start_time)->add(new DateInterval("PT".$this->time_increment."M"));

			$data = [
				"start_time" => $timeslot_start_time,
				"end_time" => $timeslot_end_time,
				"availability" => $this->id
			];

			$timeslots[] = Timeslot::create($data);

		} while($timeslot_end_time < $end_time);

		return $timeslots;
	}

}