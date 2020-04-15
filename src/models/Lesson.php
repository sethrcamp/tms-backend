<?php

class Lesson {
	public $id;
	public $student_id;
	public $instructor_id;
	public $package_id;
	public $credit_has_been_applied;
	public $homework_notes_student;
	public $homework_notes_parent;
	public $timeslot_id;
	public $date;


	public static function getAll() : array {
		$sql = "SELECT * FROM lessons";

		$db = Database::getInstance();
		$query = $db->prepare($sql);
		$query->execute();
		$result = $query->fetchAll(PDO::FETCH_CLASS, Lesson::class);
		$query->closeCursor();

		return $result;
	}

	public static function getAllByInstructorId(int $instructor_id) : array {
		$sql = "SELECT * FROM lessons WHERE instructor_id = ?";

		$db = Database::getInstance();
		$query = $db->prepare($sql);
		$query->execute([$instructor_id]);
		$result = $query->fetchAll(PDO::FETCH_CLASS, Lesson::class);
		$query->closeCursor();

		return $result;
	}

	public static function getAllByStudentId(int $student_id) : array {
		$sql = "SELECT * FROM lessons WHERE student_id = ?";

		$db = Database::getInstance();
		$query = $db->prepare($sql);
		$query->execute([$student_id]);
		$result = $query->fetchAll(PDO::FETCH_CLASS, Lesson::class);
		$query->closeCursor();

		return $result;
	}

	public static function getAllByPackageId(int $package_id) : array {
		$sql = "SELECT * FROM lessons WHERE package_id = ?";

		$db = Database::getInstance();
		$query = $db->prepare($sql);
		$query->execute([$package_id]);
		$result = $query->fetchAll(PDO::FETCH_CLASS, Lesson::class);
		$query->closeCursor();

		return $result;
	}

	public static function getById(int $id) : ?Lesson {
		$sql = "SELECT * FROM lessons WHERE id = ?";

		$db = Database::getInstance();
		$query = $db->prepare($sql);
		$query->execute([$id]);
		$result = $query->fetchAll(PDO::FETCH_CLASS, Lesson::class);
		$query->closeCursor();

		if(sizeof($result) === 0) {
			return null;
		}
		return $result[0];
	}

	public static function getAllByDay(int $begining_of_day, int $end_of_day) {
		//Put in controller
		// $beginOfDay = strtotime("today", $date);
		// $endOfDay   = strtotime("tomorrow", $beginOfDay) - 1;

		$sql = "SELECT * FROM lessons WHERE start_time > :begining_of_day AND end_time < :end_of_day";
		
		$db = Database::getInstance();
		$query = $db->prepare($sql);
		$query->execute([":begining_of_day" => $begining_of_day, ":end_of_day" => $end_of_day]);
		$result = $query->fetchAll(PDO::FETCH_CLASS, 'Lesson');
		$query->closeCursor();

		if(sizeof($result) === 0) {
			return null;
		}
		return $result;
	}

	
	public static function getAllByMonth(int $month) {
		$sql = "SELECT * FROM lessons WHERE MONTH(FROM_UNIXTIME(start_time)) = ? AND YEAR(FROM_UNIXTIME(start_time)) = YEAR(CURDATE())";
		
		$db = Database::getInstance();
		$query = $db->prepare($sql);
		$query->execute([$month]);
		$result = $query->fetchAll(PDO::FETCH_CLASS, 'Lesson');
		$query->closeCursor();

		if(sizeof($result) === 0) {
			return null;
		}
		return $result;
	}

	public static function getAllByMonthAndYear(int $month, int $year) {
		$sql = "SELECT * FROM lessons WHERE MONTH(FROM_UNIXTIME(start_time)) = ? AND YEAR(FROM_UNIXTIME(start_time)) = YEAR(?)";
		
		$db = Database::getInstance();
		$query = $db->prepare($sql);
		$query->execute([$month, $year]);
		$result = $query->fetchAll(PDO::FETCH_CLASS, 'Lesson');
		$query->closeCursor();

		if(sizeof($result) === 0) {
			return null;
		}
		return $result;
	}

	public static function getAllByType(string $service_name) : array {
		$sql = "SELECT * FROM lesson 
					JOIN users
						 ON lesson.student_id = users.id 
					JOIN user_services
						 ON users.id = user_services.user_id 
					JOIN services 
						ON user_services.service_id = services.id 
					WHERE services.name = ?";

		$db = Database::getInstance();
		$query = $db->prepare($sql);
		$query->execute([$service_name]);
		$result = $query->fetchAll(PDO::FETCH_CLASS, 'Lesson');
		$query->closeCursor();

		return $result;
	}

	public static function create($data) {
		$sql = "
            INSERT INTO lessons (
			    student_id, 
				instructor_id, 
				package_id, 
			    timeslot_id,
				date
            ) VALUES (?,?,?,?,?)
        ";
		$args = [
			$data["student_id"],
			$data['instructor_id'],
			$data['package_id'],
			$data['timeslot_id'],
			$data['date']
		];

		$db = Database::getInstance();
		$query = $db->prepare($sql);
		$query->execute($args);
		$query->closeCursor();

		return Lesson::getById($db->lastInsertId());
	}

	public function update($data) {

		$sql = "
            UPDATE lessons
            SET 
				student_id = ?,
				instructor_id= ?,
				DAY_OF_WEEK = ?,
				start_time = ?,
				end_time = ?,
				email = ?,
				time_increment = ?
            WHERE 
                id = ?
        ";



		$args = [
			$data["student_id"]		?? $this->student_id,
			$data['instructor_id']	?? $this->instructor_id,
			$data['DAY_OF_WEEK']	?? $this->DAY_OF_WEEK,
			$data['start_time']		?? $this->start_time,
			$data['end_time']		?? $this->end_time,
			$data['email']			?? $this->email,
			$data['time_increment'] ?? $this->time_increment,
			$this->id
		];

		$db = Database::getInstance();
		$query = $db->prepare($sql);
		$query->execute($args);

		$result = Lesson::getById($this->id);

		return $result;
	}

	public function delete() {
		$sql = "DELETE FROM lessons WHERE id = ?";

		$db = Database::getInstance();
		$query = $db->prepare($sql);
		$query->execute([$this->id]);
		$query->closeCursor();
	}
}