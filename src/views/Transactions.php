<?php

use Slim\Routing\RouteCollectorProxy;

$app->group('/transactions', function(RouteCollectorProxy $group){

	$group->get('', TransactionController::class.':getAll');
	$group->get('/{id}', TransactionController::class.':getById');

	$group->post('', TransactionController::class.":create");
	
});