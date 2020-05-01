<?php

use Slim\Routing\RouteCollectorProxy;

$app->group('/services', function(RouteCollectorProxy $group){

	$group->get('', ServiceController::class.':getAll');
	$group->get('/{id}', ServiceController::class.':getById');
	$group->get('/is-private/{is_private}', ServiceController::class.':getAllForIsPrivate');

	$group->post('', ServiceController::class.":create");

	$group->put('/{id}', ServiceController::class.":update");

	$group->delete('/{id}', ServiceController::class.":delete");

});