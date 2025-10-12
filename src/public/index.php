<?php
declare(strict_types=1);

require __DIR__ . '/classes/Infraestructure/Autoload/Autoloader.php';
\App\Infraestructure\Autoload\Autoloader::register('App\\', __DIR__ . '/classes');

use App\Presentation\Controllers\GameController;

$config = require __DIR__ . '/config/config.php';

$controller = new GameController($config);
$controller->handle();