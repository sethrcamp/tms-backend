<?php


class Session {

	public $id;
	public $user_id;
	public $session_key;
	public $created_time;
	public $last_login_time;
	public $is_canceled;
	public $extension_count;

	public static function create(User $user) : Session {
		$sql = "
            INSERT INTO sessions (
				user_id,
                session_key
            ) VALUES (?,?)
        ";
		$args = [
			$user->id,
			Helper::generateToken()
		];

		$db = Database::getInstance();
		$query = $db->prepare($sql);
		$query->execute($args);
		$query->closeCursor();

		return Session::getById($db->lastInsertId());
	}

	private static function getById(int $id) {
		$sql = "SELECT * FROM sessions WHERE id = ?";

		$db = Database::getInstance();
		$query = $db->prepare($sql);
		$query->execute([$id]);
		$result = $query->fetchAll(PDO::FETCH_CLASS, 'Session');
		$query->closeCursor();

		if(sizeof($result) === 0) {
			return null;
		}
		return $result[0];
	}

	public static function getByIdAndKey(int $id, string $key) : Session {
		$sql = "SELECT * FROM sessions WHERE id = ? AND session_key = ?";

		$db = Database::getInstance();
		$query = $db->prepare($sql);
		$query->execute([$id, $key]);
		$result = $query->fetchAll(PDO::FETCH_CLASS, 'Session');
		$query->closeCursor();

		if(sizeof($result) === 0) {
			return null;
		}
		return $result[0];
	}

	public static function getCurrentSessionByUser(User $user) : ?Session {
		$sql = "SELECT * FROM sessions WHERE user_id = ? ORDER BY created_time DESC LIMIT 1";

		$db = Database::getInstance();
		$query = $db->prepare($sql);
		$query->execute([$user->id]);
		$result = $query->fetchAll(PDO::FETCH_CLASS, 'Session');
		$query->closeCursor();

		if(sizeof($result) === 0) {
			return null;
		}
		return $result[0];
	}

	public function extend() : Session {
		$sql = "
            UPDATE sessions
            SET last_login_time = CURRENT_TIMESTAMP,
            	extension_count = ?
            WHERE id = ?
        ";

		$args = [
			$this->extension_count+1,
			$this->id
		];

		$db = Database::getInstance();
		$query = $db->prepare($sql);
		$query->execute($args);

		$result = Session::getById($this->id);

		return $result;
	}

	public function cancel() : Session {
		$sql = "
            UPDATE sessions
            SET is_canceled = 1
            WHERE id = ?
        ";

		$db = Database::getInstance();
		$query = $db->prepare($sql);
		$query->execute([$this->id]);

		$result = Session::getById($this->id);

		return $result;
	}

	public function hasExpired() : bool {
		$now = new DateTime();
		$session_expiration_time = $now->sub(new DateInterval("PT".SESSION_LENGTH_IN_MINUTES."M"));
		$last_login_time = new DateTime($this->last_login_time);

		$session_created_time = new DateTime($this->created_time);
		$max_extension_time = $session_created_time->add(new DateInterval("PT".MAX_SESSION_EXTENSION_LENGTH_IN_MINUTES."M"));

		return ($last_login_time < $session_expiration_time) || ($now > $max_extension_time) || $this->is_canceled;
	}

	public function getUser() {
		return User::getById($this->user_id);
	}

}