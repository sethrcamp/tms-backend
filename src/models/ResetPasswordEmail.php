<?php


class ResetPasswordEmail {
	public $id;
	public $user_id;
	public $created_time;

	public static function getById(string $id) : ?ResetPasswordEmail {
		$sql = "SELECT * FROM reset_password_emails WHERE id = ?";

		$db = Database::getInstance();
		$query = $db->prepare($sql);
		$query->execute([$id]);
		$result = $query->fetchAll(PDO::FETCH_CLASS, ResetPasswordEmail::class);
		$query->closeCursor();

		if(sizeof($result) === 0) {
			return null;
		}
		return $result[0];
	}

	public static function create(array $data) : ResetPasswordEmail {
		$sql = "
            INSERT INTO reset_password_emails (id, user_id) VALUES (?,?)
        ";
		$args = [
			$data['id'],
			$data['user_id']
		];

		$db = Database::getInstance();
		$query = $db->prepare($sql);
		$query->execute($args);
		$query->closeCursor();

		return ResetPasswordEmail::getById($data['id']);
	}
}