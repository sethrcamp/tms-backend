<?php

use Slim\Routing\RouteCollectorProxy;

$app->group('/rentals', function(RouteCollectorProxy $group){

	$group->get('', RentalController::class.':getAll');
	$group->get('/{id}', RentalController::class.':getById');
	$group->get('/rate/{rate_id}', RentalController::class.':getAllByRateId');
	$group->get('/user/{user_id}', RentalController::class.':getAllByUserId');

	$group->post('', RentalController::class.":create");

	$group->put('/{id}', RentalController::class.":update");

	$group->delete('/{id}', RentalController::class.":delete");

});