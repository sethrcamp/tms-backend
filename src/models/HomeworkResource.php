<?php


class HomeworkResource {
	public $id;
	public $lesson_id;
	public $url;

	public static function getAll() : array {
		$sql = "SELECT * FROM homework_resources";

		$db = Database::getInstance();
		$query = $db->prepare($sql);
		$query->execute();
		$result = $query->fetchAll(PDO::FETCH_CLASS, HomeworkResource::class);
		$query->closeCursor();

		return $result;
	}

	public static function getById(int $id) : ?HomeworkResource {
		$sql = "SELECT * FROM homework_resources WHERE id = ?";

		$db = Database::getInstance();
		$query = $db->prepare($sql);
		$query->execute([$id]);
		$result = $query->fetchAll(PDO::FETCH_CLASS, HomeworkResource::class);
		$query->closeCursor();

		if(sizeof($result) === 0) {
			return null;
		}
		return $result[0];
	}

	public static function create(array $data) : HomeworkResource {
		$sql = "
            INSERT INTO homework_resources (lesson_id, url) VALUES (?,?) 
        ";
		$args = [
			$data['lesson_id'],
			$data['url']
		];

		$db = Database::getInstance();
		$query = $db->prepare($sql);
		$query->execute($args);
		$query->closeCursor();

		return HomeworkResource::getById($db->lastInsertId());
	}

	public static function getAllByLessonId($id) : array {
		$sql = "SELECT * FROM homework_resources WHERE lesson_id = ?";

		$db = Database::getInstance();
		$query = $db->prepare($sql);
		$query->execute([$id]);
		$results = $query->fetchAll(PDO::FETCH_CLASS, HomeworkResource::class);
		$query->closeCursor();

		return $results;
	}

	public function update(array $data) : HomeworkResource {

		$sql = "
            UPDATE homework_resources
            SET url = ?
            WHERE id = ?
        ";


		$args = [
			$data['url'],
			$this->id
		];

		$db = Database::getInstance();
		$query = $db->prepare($sql);
		$query->execute($args);

		$result = HomeworkResource::getById($this->id);

		return $result;
	}

	public function delete() : void {
		$sql = "DELETE FROM homework_resources WHERE id = ?";

		$db = Database::getInstance();
		$query = $db->prepare($sql);
		$query->execute([$this->id]);
		$query->closeCursor();
	}
}