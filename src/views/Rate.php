<?php

use Slim\Routing\RouteCollectorProxy;

$app->group('/rates', function(RouteCollectorProxy $group){

	$group->get('', RateController::class.':getAll');
	$group->get('/{id}', RateController::class.':getById');
	$group->get('/type/{type}', RateController::class.':getAllByType');
	$group->get('/service/{service_id}', RateController::class.':getAllByServiceId');
	$group->get('/service/{service_id}/type/{type}', RateController::class.':getAllByServiceIdAndType');
	$group->get('/type/{type}/service/{service_id}', RateController::class.':getAllByServiceIdAndType'); //alias of above endpoint
	$group->get('/service/{service_id}/type/{type}/timing/{timing}', RateController::class.':getAllByServiceIdAndTypeAndTiming');

	$group->post('', RateController::class.":create");

	$group->put('/{id}', RateController::class.":update");

	$group->delete('/{id}', RateController::class.":delete");

});