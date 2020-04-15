<?php

use Slim\Routing\RouteCollectorProxy;

$app->group('/lessons', function(RouteCollectorProxy $group){

	$group->get('', LessonController::class.':getAll');
	$group->get('/{id}', LessonController::class.':getById');
	$group->get('/type/{type}', LessonController::class.':getAllByType');

	$group->post('', LessonController::class.":create");
	$group->post('/term', LessonController::class.":createForTerm");

	$group->put('/{id}', LessonController::class.":update");

	$group->delete('/{id}', LessonController::class.":delete");

});