<?php
require_once "TestHelper.php";
use core\Rack;
use core\App;

class MyMiddleware extends App
{
  public function __invoke($env)
  {
    return array(200, array("Content-Type" => "text/html"), array('Hello world!'));
  }
}

class MyLowercaseMiddleware extends App
{
  public function __invoke($env)
  {
    return array(200, array("Content-Type" => "text/html"), array('this is in lowercase'));
  }
}

class MyUppercaseMiddleware extends App
{
  protected $app;
  
  public function __construct($app)
  {
    $this->app = $app;
  }
  
  public function __invoke($env)
  {
    list($status, $headers, $response) = $this->call($this->app, $env);
    $uppercase_response = array(strtoupper($response[0]));
    return array($status, $headers, $uppercase_response);
  }
}

class RackTest extends PHPUnit_Framework_TestCase
{
  public function tearDown()
  {
    Rack::clearMiddleware();
  }
  
  public function testMiddlewareReturnsEmptyArray()
  {
    $this->assertEquals(array(), Rack::middleware());
  }
}