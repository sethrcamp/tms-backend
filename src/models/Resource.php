<?php

require_once __DIR__."/../helper/Enum.php";

abstract class TMSResourceType extends Enum {
	const URL = "URL";
	const FILE = "FILE";
	const TEXT = "TEXT";
}

class TMSResource {

	public $id;
	public $user_id;
	public $content;
	public $type;

	public static function getAll() : array {
		$sql = "SELECT * FROM resources";

		$db = Database::getInstance();
		$query = $db->prepare($sql);
		$query->execute();
		$result = $query->fetchAll(PDO::FETCH_CLASS, 'TMSResource');
		$query->closeCursor();

		return $result;
	}

	public static function getById(int $id) : ?TMSResource {
		$sql = "SELECT * FROM resources WHERE id = ?";

		$db = Database::getInstance();
		$query = $db->prepare($sql);
		$query->execute([$id]);
		$result = $query->fetchAll(PDO::FETCH_CLASS, 'TMSResource');
		$query->closeCursor();

		if(sizeof($result) === 0) {
			return null;
		}
		return $result[0];
	}

	public static function getAllByUser(User $user) : array {
		$sql = "SELECT * FROM resources WHERE user_id = ?";

		$db = Database::getInstance();
		$query = $db->prepare($sql);
		$query->execute([$user->id]);
		$result = $query->fetchAll(PDO::FETCH_CLASS, 'TMSResource');
		$query->closeCursor();

		return $result;
	}

	public static function getAllByType(string $resource_type) : array {
		$sql = "SELECT * FROM resources WHERE type = ?";

		$db = Database::getInstance();
		$query = $db->prepare($sql);
		$query->execute([$resource_type]);
		$result = $query->fetchAll(PDO::FETCH_CLASS, 'TMSResource');
		$query->closeCursor();

		return $result;
	}

	public function getUser() : User {
		return User::getById($this->user_id);
	}

	public static function create($data) : TMSResource {
		$sql = "
            INSERT INTO resources (
				user_id, 
			    content, 
			    type
            ) VALUES (?,?,?)
        ";
		$args = [
			$data['user_id'] ?? null,
			$data['content'],
			$data['type']
		];

		$db = Database::getInstance();
		$query = $db->prepare($sql);
		$query->execute($args);
		$query->closeCursor();

		return TMSResource::getById($db->lastInsertId());
	}

	public function update($data) : TMSResource {

		$sql = "
            UPDATE resources
            SET 
                user_id = ?,
				content = ?,
				type = ?
            WHERE 
                id = ?
        ";


		$args = [
			(array_key_exists('user_id', $data) && $data['user_id'] === null) ? null : ($data['user_id'] ?? $this->user_id), //if the data is set to null, use null, if it is missing, use the original, otherwise use whatever it is set to
			$data['content'] ?? $this->content,
			$data['type'] ?? $this->type,
			$this->id
		];

		$db = Database::getInstance();
		$query = $db->prepare($sql);
		$query->execute($args);

		$result = TMSResource::getById($this->id);

		return $result;
	}

	public function delete() : void {
		$sql = "DELETE FROM resources WHERE id = ?";

		$db = Database::getInstance();
		$query = $db->prepare($sql);
		$query->execute([$this->id]);
		$query->closeCursor();
	}
}