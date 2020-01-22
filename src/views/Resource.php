<?php

use Slim\Routing\RouteCollectorProxy;

$app->group('/resources', function(RouteCollectorProxy $group){

	$group->get('', TMSResourceController::class.':getAll');
	$group->get('/{id}', TMSResourceController::class.':getById');
	$group->get('/type/{type}', TMSResourceController::class.':getAllByType');
	$group->get('/user/{user_id}', TMSResourceController::class.':getAllByUserId');

	$group->post('', TMSResourceController::class.":create");

	$group->put('/{id}', TMSResourceController::class.":update");

	$group->delete('/{id}', TMSResourceController::class.":delete");

});