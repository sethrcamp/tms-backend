<?php

require_once __DIR__."/../helper/Enum.php";

abstract class UserType extends Enum {
	const SUPER_ADMIN = "SUPER_ADMIN";
	const ADMIN = "ADMIN";
	const STUDENT = "STUDENT";
}

abstract class UserAgeClassification extends Enum {
	const ADULT = "ADULT";
	const MINOR = "MINOR";
	const CHILD = "CHILD";
}

class User {
	public $id;
	public $parent_id;
	public $type;
	public $age_classification;
	public $first_name;
	public $last_name;
	public $email;
	private $password;
	public $credit;
	public $birthday;
	public $signature;


	public static function getAll() : array {
		$sql = "SELECT * FROM users";

		$db = Database::getInstance();
		$query = $db->prepare($sql);
		$query->execute();
		$result = $query->fetchAll(PDO::FETCH_CLASS, 'User');
		$query->closeCursor();

		return $result;
	}

	public static function getById(int $id) : ?User {
		$sql = "SELECT * FROM users WHERE id = ?";

		$db = Database::getInstance();
		$query = $db->prepare($sql);
		$query->execute([$id]);
		$result = $query->fetchAll(PDO::FETCH_CLASS, 'User');
		$query->closeCursor();

		if(sizeof($result) === 0) {
			return null;
		}
		return $result[0];
	}

	public static function getByEmail(string $email) : ?User {
		$sql = "SELECT * FROM users WHERE email = ?";

		$db = Database::getInstance();
		$query = $db->prepare($sql);
		$query->execute([$email]);
		$result = $query->fetchAll(PDO::FETCH_CLASS, 'User');
		$query->closeCursor();

		if(sizeof($result) === 0) {
			return null;
		}
		return $result[0];
	}

	public static function getAllByType(string $type) : array {
		$sql = "SELECT * FROM users WHERE type = ?";

		$db = Database::getInstance();
		$query = $db->prepare($sql);
		$query->execute([$type]);
		$result = $query->fetchAll(PDO::FETCH_CLASS, 'User');
		$query->closeCursor();

		return $result;
	}

	public static function getAllByAgeClassification(string $age_classification) : array {
		$sql = "SELECT * FROM users WHERE age_classification = ?";

		$db = Database::getInstance();
		$query = $db->prepare($sql);
		$query->execute([$age_classification]);
		$result = $query->fetchAll(PDO::FETCH_CLASS, 'User');
		$query->closeCursor();

		return $result;
	}

	public function getParent() : User { //for the future
		return User::getById($this->parent_id);
	}

	public static function create(array $data) : User {
		$sql = "
            INSERT INTO users (
				parent_id,
				type,
				age_classification,
				first_name,
				last_name,
				email,
				password,
				birthday
            ) VALUES (?,?,?,?,?,?,?,?)
        ";
		$args = [
			$data['parent_id'] ?? null,
			$data['type'] ?? UserType::STUDENT,
			$data['age_classification'],
			$data['first_name'],
			$data['last_name'],
			$data['email'],
			$data['password'],
			$data['birthday']
		];

		$db = Database::getInstance();
		$query = $db->prepare($sql);
		$query->execute($args);
		$query->closeCursor();

		return User::getById($db->lastInsertId());
	}

	public function update(array $data) : User {

		$sql = "
            UPDATE users
            SET 
                parent_id = ?,
				type = ?,
				age_classification = ?,
				first_name = ?,
				last_name = ?,
				email = ?,
				password = ?,
				credit = ?,
				birthday = ?,
				signature = ?
            WHERE 
                id = ?
        ";



		$args = [
			(array_key_exists('parent_id', $data) && $data['parent_id'] === null) ? null : ($data['parent_id'] ?? $this->parent_id), //if the data is set to null, use null, if it is missing, use the original, otherwise use whatever it is set to
			$data['type']               ?? $this->type,
			$data['age_classification'] ?? $this->age_classification,
			$data['first_name']         ?? $this->first_name,
			$data['last_name']          ?? $this->last_name,
			$data['email']              ?? $this->email,
			$data['password']           ?? $this->password,
			$data['credit']             ?? $this->credit,
			$data['birthday']           ?? $this->birthday,
			$data['signature']          ?? $this->signature,
			$this->id
		];

		$db = Database::getInstance();
		$query = $db->prepare($sql);
		$query->execute($args);

		$result = User::getById($this->id);

		return $result;
	}

	public function delete() : void {
		$sql = "DELETE FROM users WHERE id = ?";

		$db = Database::getInstance();
		$query = $db->prepare($sql);
		$query->execute([$this->id]);
		$query->closeCursor();
	}

	public function verifyPassword(string $password) : void {
		if(!password_verify($password, $this->password)) {
			throw new InvalidCredentialsException();
		}
	}

	public function getCurrentSession() : ?Session {
		return Session::getCurrentSessionByUser($this);
	}
}