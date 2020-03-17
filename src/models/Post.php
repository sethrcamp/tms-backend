<?php


class Post {

	public $id;
	public $author_id;
	public $title;
	public $posted_time;
	public $content;

	public static function getAll() : array {
		$sql = "SELECT * FROM posts ORDER BY posted_time";

		$db = Database::getInstance();
		$query = $db->prepare($sql);
		$query->execute();
		$result = $query->fetchAll(PDO::FETCH_CLASS, 'Post');
		$query->closeCursor();

		return $result;
	}

	public static function getById(int $id) : ?Post {
		$sql = "SELECT * FROM posts WHERE id = ?";

		$db = Database::getInstance();
		$query = $db->prepare($sql);
		$query->execute([$id]);
		$result = $query->fetchAll(PDO::FETCH_CLASS, 'Post');
		$query->closeCursor();

		if(sizeof($result) === 0) {
			return null;
		}
		return $result[0];
	}

	public static function getAllByAuthor(User $author) : array {
		$sql = "SELECT * FROM posts WHERE author_id = ? ORDER BY posted_time";

		$db = Database::getInstance();
		$query = $db->prepare($sql);
		$query->execute([$author->id]);
		$result = $query->fetchAll(PDO::FETCH_CLASS, 'Post');
		$query->closeCursor();

		return $result;
	}

	public static function getAllWithinRange(DateTime $start_time, DateTime $end_time) : array {
		$sql = "SELECT * FROM posts WHERE posted_time BETWEEN ? AND ? ORDER BY posted_time";

		$db = Database::getInstance();
		$query = $db->prepare($sql);
		$query->execute([$start_time->format(DB_DATETIME_FORMAT), $end_time->format(DB_DATETIME_FORMAT)]);
		$result = $query->fetchAll(PDO::FETCH_CLASS, 'Post');
		$query->closeCursor();

		return $result;
	}

	public static function create($data) : Post {
		$sql = "
            INSERT INTO posts (
				author_id,
				title,
				posted_time,
				content
            ) VALUES (?,?,?,?)
        ";
		$args = [
			$data['author_id'],
			$data['title'],
			$data['posted_time']->format(DB_DATETIME_FORMAT),
			$data['content']
		];

		$db = Database::getInstance();
		$query = $db->prepare($sql);
		$query->execute($args);
		$query->closeCursor();

		return Post::getById($db->lastInsertId());
	}

	public function delete() : void {
		$sql = "DELETE FROM posts WHERE id = ?";

		$db = Database::getInstance();
		$query = $db->prepare($sql);
		$query->execute([$this->id]);
		$query->closeCursor();
	}
}