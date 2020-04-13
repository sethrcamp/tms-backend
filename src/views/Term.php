<?php

use Slim\Routing\RouteCollectorProxy;

$app->group('/terms', function(RouteCollectorProxy $group){

	$group->get('', TermController::class.':getAll');
	$group->get('/open', TermController::class.":getAllOpen");
	$group->get('/{id}', TermController::class.':getById');
	$group->get('/date/{date}', TermController::class.':getForDate');

	$group->post('', TermController::class.":create");

	$group->put('/{id}', TermController::class.":update");

	$group->delete('/{id}', TermController::class.":delete");

});