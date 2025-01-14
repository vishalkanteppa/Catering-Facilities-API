<?php

/** @var Bramus\Router\Router $router */

// Define routes here
$router->get('/test', App\Controllers\IndexController::class . '@test');
$router->get('/', App\Controllers\IndexController::class . '@test');
$router->get('/facilities/search', \App\Controllers\FacilitiesController::class . '@searchFacilitiesByFilter');
$router->get('/facilities/{fname}', App\Controllers\FacilitiesController::class . '@getFacility');
$router->get('/facilities', App\Controllers\FacilitiesController::class . '@getAllFacilities');
$router->post('/facilities', App\Controllers\FacilitiesController::class . '@createFacility');
$router->put('/facilities', App\Controllers\FacilitiesController::class . '@updateFacility');
$router->delete('/facilities/{name}', App\Controllers\FacilitiesController::class . '@deleteFacility');

