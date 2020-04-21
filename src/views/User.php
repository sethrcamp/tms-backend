<?php

use Slim\Routing\RouteCollectorProxy;

$app->group('/users', function(RouteCollectorProxy $group){

	$group->get('', UserController::class.':getAll');
	$group->get('/{id}', UserController::class.':getById');
	$group->get('/type/{type}', UserController::class.':getAllByType');
	$group->get('/age_classification/{age_classification}', UserController::class.':getAllByAgeClassification');
	$group->get('/reset-password-email-id/{id}', UserController::class.':getByResetPasswordEmailId');

	
	$group->post('', UserController::class.":create");
	$group->post('/send-reset-password-email', UserController::class.":sendResetPasswordEmail");

	$group->put('/login', UserController::class.":login");
	$group->put('/logout', UserController::class.":logoutCurrent")->add(new Validator());
	$group->put('/logout/{id}', UserController::class.":logout");
	$group->put('/{id}', UserController::class.":update");
	$group->put('/{id}/reset-password', UserController::class.":resetPassword");

	$group->delete('/{id}', UserController::class.":delete");

});