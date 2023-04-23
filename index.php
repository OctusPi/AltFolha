<?php
header("Cache-Control: no-cache, no-store, must-revalidate"); // HTTP 1.1.
header("Pragma: no-cache"); // HTTP 1.0.
header("Expires: 0 "); // Proxies.

require __DIR__ . '/vendor/autoload.php';

use Octus\App\Utils\Route;
use Octus\App\Utils\Session;

$session = new Session();
$route   = new Route($session);
echo $route->go();