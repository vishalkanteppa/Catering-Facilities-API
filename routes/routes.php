<?php

/** @var Bramus\Router\Router $router */

// Define routes here
$router->get('/test', App\Controllers\IndexController::class . '@test');
$router->get('/', App\Controllers\IndexController::class . '@test');
$router->get('/read_facilities/{fname}', App\Controllers\FacilitiesController::class . '@getFacility');
$router->get('/read_facilities', App\Controllers\FacilitiesController::class . '@getAllFacilities');
$router->post('/create_facility', App\Controllers\FacilitiesController::class . '@createFacility');
$router->put('/update_facility', App\Controllers\FacilitiesController::class . '@updateFacility');
$router->delete('/delete_facility/{name}', App\Controllers\FacilitiesController::class . '@deleteFacility');
$router->get('/search_facilities', \App\Controllers\FacilitiesController::class . '@searchFacilitiesByFilter');

