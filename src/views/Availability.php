<?php

use Slim\Routing\RouteCollectorProxy;

$app->group('/availabilities', function(RouteCollectorProxy $group){

	$group->get('', AvailabilityController::class.':getAll');
	$group->get('/{id}', AvailabilityController::class.':getById');
	$group->get('/open-timeslots/term/{term_id}', AvailabilityController::class.':getAllOpenTimeslotsByTermId');

	$group->post('', AvailabilityController::class.":create");

	$group->put('/{id}', AvailabilityController::class.":update");

	$group->delete('/{id}', AvailabilityController::class.":delete");

});