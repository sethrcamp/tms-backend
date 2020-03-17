<?php

use Slim\Psr7\Request;
use Slim\Psr7\Response;

class PostController {

	public static function getAll(Request $request, Response $response, array $args) {
		$posts = Post::getAll();
		$result = [
			"posts" => $posts
		];

		$response = new JsonResponse($response);
		return $response->withJson($result);
	}

	public static function getById(Request $request, Response $response, array $args) {
		$post = Post::getById($args['id']);

		if($post === null) {
			throw new ItemNotFoundException("post", "id: ".$args['id']);
		}

		$result = [
			"post" => $post
		];

		$response = new JsonResponse($response);
		return $response->withJson($result);
	}

	public static function getAllByAuthorId(Request $request, Response $response, array $args) {

		$author = User::getById($args['author_id']);

		if(!$author) {
			throw new ItemNotFoundException("user", "id: ".$args['author_id']);
		}

		$posts = Post::getAllByAuthor($author);
		$result = [
			"posts" => $posts
		];

		$response = new JsonResponse($response);
		return $response->withJson($result);
	}

	public static function getAllWithinRange(Request $request, Response $response, array $args) {

		$start_time = urldecode($args['start_time']);
		$end_time = urldecode($args['end_time']);

		try {
			$start_time = new DateTime($start_time);
		} catch (Exception $e) {
			if($e->getCode() === 0) {
				throw new IncorrectTypeException($start_time, 'DateTime format');
			}
		}

		try {
			$end_time = new DateTime($end_time);
		} catch (Exception $e) {
			if($e->getCode() === 0) {
				throw new IncorrectTypeException($end_time, 'DateTime format');
			}
		}

		$posts = Post::getAllWithinRange($start_time, $end_time);
		$result = [
			"posts" => $posts
		];

		$response = new JsonResponse($response);
		return $response->withJson($result);

	}

	public static function create(Request $request, Response $response, array $args) {
		$body = $request->getParsedBody();

		$required_parameters = [
			'author_id',
			'title',
			'posted_time',
			'content'
		];

		Helper::checkForAllParameters($body, $required_parameters);


		$author = User::getById($body['author_id']);

		if(!$author) {
			throw new ItemNotFoundException("user", "id: ".$body['author_id']);
		}

		$posted_time = $body['posted_time'];

		try {
			$posted_time = new DateTime($posted_time);
		} catch (Exception $e) {
			if($e->getCode() === 0) {
				throw new IncorrectTypeException($posted_time, 'DateTime format');
			}
		}

		$body['posted_time'] = $posted_time;

		$post = Post::create($body);

		$result = [
			"post" => $post
		];

		$response = new JsonResponse($response);
		return $response->withJson($result);
	}

	public static function delete(Request $request, Response $response, array $args) {
		$post = Post::getById($args['id']);

		if(!$post) {
			throw new ItemNotFoundException("post", "id: ".$args['id']);
		}

		$post->delete();

		$result = [
			"post" => $post
		];

		$response = new JsonResponse($response);
		return $response->withJson($result);
	}


}