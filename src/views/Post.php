<?php

use Slim\Routing\RouteCollectorProxy;

$app->group('/posts', function(RouteCollectorProxy $group){

	$group->get('', PostController::class.':getAll');
	$group->get('/{id}', PostController::class.':getById');
	$group->get('/author/{author_id}', PostController::class.':getAllByAuthorId');
	$group->get('/start/{start_time}/end/{end_time}', PostController::class.':getAllWithinRange');

	$group->post('', PostController::class.":create");

	$group->put('/{id}', PostController::class.":update");

	$group->delete('/{id}', PostController::class.":delete");

});