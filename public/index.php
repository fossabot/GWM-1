<?php

use GWM\Core\Router;
use GWM\Core\Utils\Debug;

!defined('GWM') ? define('GWM',
[
    'DIR_ROOT' => dirname(__DIR__),
    'DIR_PUBLIC' => dirname(__DIR__).'/public',
    'DIR_TMP' => dirname(__DIR__).'/tmp',
    'START_TIME' => microtime(true),
    'ERROR_LEVEL' => error_reporting(E_ALL)
]) : exit;

if(version_compare(PHP_VERSION, '7.4.0') < 0) exit;

chdir(GWM['DIR_ROOT']);

require_once 'vendor/index.php';

function exception_handler($exception) {
    die("Exception Caught: ". $exception->getMessage() ."\n");
}

function error_handler($errno, $errstr, $errfile, $errline)
{
    $errstr = htmlspecialchars($errstr);

    Debug::$log[] = "Error[$errno] - $errstr, Line - $errline, File - $errfile";

    return true;
}

set_error_handler('error_handler');
set_exception_handler('exception_handler');

chdir(GWM['DIR_ROOT']);

if (file_exists('.env') == false) {
    $generated = bin2hex(random_bytes(5));

    file_put_contents('.env', <<<EOF
    DB_DRIVER=
    DB_HOST=
    DB_USERNAME=
    DB_PASSWORD=
    DB_PREFIX=$generated
EOF);
    
    trigger_error('You need to update .env variables!');
    exit;
}

$dotenv = Dotenv\Dotenv::createImmutable(GWM['DIR_ROOT']);

$dotenv->load();

$dotenv->required([
    'DB_DRIVER',
    'DB_HOST',
    'DB_USERNAME',
    'DB_PASSWORD',
    'DB_NAME',
    'DB_PREFIX'
]);

new \GWM\Core\Bootstrap();

$response = new GWM\Core\Response();

$router = new GWM\Core\Router();
$router->Resolve($response);

$latte = new \Latte\Engine;
$latte->setTempDirectory('tmp/latte');
$err404 = $latte->renderToString('themes/default/templates/404.latte');

$response->setContent($err404)->send(404);

?>