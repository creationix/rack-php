<?php
require_once "PHPUnit/Framework.php";
require_once realpath(dirname(__FILE__).'/../../lib/autoload.php');
use core\Rack;
use core\App;

class MockApp extends App
{
  public function __invoke($env)
  {
    return array(200, array('Content-Type' => 'text/html'), array('Hello World'));
  }
}

class MockRack extends Rack
{
  public static function run($app)
  {
    $env =& static::get_env();
    ob_start();
    $result = self::runMiddleware($app, $env);
    $output = ob_get_clean();
    
    if ($output) 
    {
      $result[1]["X-Output"] = json_encode($output);
    }
    return static::execute($result, $env);
  }
  
  protected static function execute($result, $env)
  {
    list($status, $headers, $body) = $result;
    fclose($env['rack.input']);
    fclose($env['rack.errors']);
    $headers['X-Powered-By'] = "rack-php ".implode('.',$env['rack.version']);
    $headers['Status'] = $status;
    return array($status, $headers, $body);
  }
}