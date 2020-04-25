<?php

class TransactionType extends Enum {
	const PAYMENT = "PAYMENT";
	const CHARGE = "CHARGE";
	const CREDIT = "CREDIT";
}

class Transaction {
	public $id;
	public $from_user_id;
	public $to_user_id;
	public $amount;
	public $type;
	public $created_time;
	public $description;
	public $lesson_id;
	public $rental_id;

	public static function getAll() : array {
		$sql = "SELECT * FROM transactions ORDER BY created_time";

		$db = Database::getInstance();
		$query = $db->prepare($sql);
		$query->execute();
		$result = $query->fetchAll(PDO::FETCH_CLASS, Transaction::class);
		$query->closeCursor();

		return $result;
	}

	public static function getById(int $id) : ?Transaction {
		$sql = "SELECT * FROM transactions WHERE id = ?";

		$db = Database::getInstance();
		$query = $db->prepare($sql);
		$query->execute([$id]);
		$result = $query->fetchAll(PDO::FETCH_CLASS, Transaction::class);
		$query->closeCursor();

		if(sizeof($result) === 0) {
			return null;
		}
		return $result[0];
	}

	public static function create(array $data) : Transaction {
		$sql = "
            INSERT INTO transactions (
				  from_user_id, 
				  to_user_id, 
				  amount, 
				  type, 
				  description, 
				  lesson_id, 
				  rental_id
			  )  VALUES (?,?,?,?,?,?,?)
				  
        ";
		$args = [
			$data['from_user_id'],
			$data['to_user_id'],
			$data['amount'],
			$data['type'],
			$data['description'],
			$data['lesson_id'] ?? null,
			$data['rental_id'] ?? null
		];

		$db = Database::getInstance();
		$query = $db->prepare($sql);
		$query->execute($args);
		$query->closeCursor();

		return Transaction::getById($db->lastInsertId());
	}
}