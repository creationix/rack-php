<?php
require realpath(dirname(__FILE__).'/../lib/autoload.php');
use core\Rack;

//$app = new creationix\MyApplication;
$app = function ($env) {
  return array(200, array('Content-Type' => 'text/html'), array('Hello World'));
};
Rack::run($app); 

