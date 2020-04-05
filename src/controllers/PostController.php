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

		$parsed_start_time = strtotime($start_time);
		if(!$parsed_start_time) {
			throw new IncorrectTypeException($start_time, 'time');
		}

		$parsed_end_time = strtotime($end_time);
		if(!$parsed_end_time) {
			throw new IncorrectTypeException($end_time, 'time');
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

		$parsed_posted_time = strtotime($body['posted_time']);

		if(!$parsed_posted_time) {
			throw new IncorrectTypeException($body['posted_time'], 'time');
		}

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