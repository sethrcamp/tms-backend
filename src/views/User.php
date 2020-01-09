<?php

use Slim\Routing\RouteCollectorProxy;

$app->group('/users', function(RouteCollectorProxy $group){

	$group->get('', UserController::class.':getAll');
	$group->get('/{id}', UserController::class.':getById');
	$group->get('/type/{type}', UserController::class.':getAllByType');
	$group->get('/age_classification/{age_classification}', UserController::class.':getAllByAgeClassification');

	$group->post('', UserController::class.":create");

	$group->put('/{id}', UserController::class.":update");

	$group->delete('/{id}', UserController::class.":delete");

});