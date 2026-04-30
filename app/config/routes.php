<?php
require_once __DIR__ . '/../Core/Router.php';
$router = new App\Core\Router();
$router->add('GET', '/', 'HomeController@index');
return $router;
