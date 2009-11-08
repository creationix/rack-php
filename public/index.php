<?
require realpath(dirname(__FILE__).'/../lib/autoload.php');
use core\Rack;

Rack::useMiddleware("creationix\MyApplication");
$env = Rack::run();

