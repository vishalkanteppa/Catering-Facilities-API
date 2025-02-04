<?php

error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE);
ini_set('display_errors', 1);


// Autoloader
require_once '../vendor/autoload.php';

// Load Config
$config = require_once '../config/config.php';

// Services
require_once '../config/services.php';

// Router
$router = require_once '../routes/router.php';

// to setup the db in the first run
use App\Db\Setup;
$setup = new Setup();
$setup->runSetup();

// Run application through router:
try {
    $router->run();
} catch (\App\Plugins\Http\ApiException $e) {
    // Send the API exception to the client:
    $e->send();
} catch (Exception $e) {
    // For debugging purposes, throw the initial exception:
    throw $e;
}
