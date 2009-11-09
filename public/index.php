<?php
require realpath(dirname(__FILE__).'/../lib/autoload.php');
use core\Rack;

$app = new creationix\MyApplication;
Rack::run($app); 

