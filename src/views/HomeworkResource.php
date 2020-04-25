<?php

use Slim\Routing\RouteCollectorProxy;

$app->group('/homework-resources', function(RouteCollectorProxy $group){

	$group->get('', HomeworkResourceController::class.':getAll');
	$group->get('/{id}', HomeworkResourceController::class.':getById');
	$group->get('/lesson/{id}', HomeworkResourceController::class.':getAllByLessonId');

	$group->post('', HomeworkResourceController::class.":create");

	$group->put('/{id}', HomeworkResourceController::class.":update");

	$group->delete('/{id}', HomeworkResourceController::class.":delete");

});