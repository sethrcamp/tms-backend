<?php

use Slim\Routing\RouteCollectorProxy;

$app->group('/rentals', function(RouteCollectorProxy $group){

	$group->get('', RentalController::class.':getAll');
	$group->get('/{id}', RentalController::class.':getById');

	$group->post('', RentalController::class.":create");

	$group->put('/{id}', RentalController::class.":update");

	$group->delete('/{id}', RentalController::class.":delete");

});