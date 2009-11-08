<?php
require_once "TestHelper.php";
use core\Rack;
use core\App;

class MyMiddleware extends App
{
  public function call($env)
  {
    return array(200, array("Content-Type" => "text/html"), array('Hello world!'));
  }
}

class MyLowercaseMiddleware extends App
{
  public function call($env)
  {
    return array(200, array("Content-Type" => "text/html"), array('this is in lowercase'));
  }
}

class MyUppercaseMiddleware extends App
{
  public function __construct($app)
  {
    $this->app = $app;
  }
  
  public function call($env)
  {
    list($status, $headers, $response) = $this->app->call($env);
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
  
  public function testUseMiddleware()
  {
    $my_middleware = "MyMiddleware";
    $my_another_middleware = "MyAnotherMiddleware";
    
    Rack::useMiddleware($my_middleware);
    $this->assertEquals(1, count(Rack::middleware()));
    Rack::useMiddleware($my_another_middleware);
    $this->assertEquals(2, count(Rack::middleware()));
    
    $middleware = Rack::middleware();
    $this->assertEquals($my_middleware, $middleware[0]);
    $this->assertEquals($my_another_middleware, $middleware[1]);
  }
  
  public function testRunMiddlewareReturnsMyMiddlewareResponse()
  {
    Rack::useMiddleware("MyMiddleware");
    list($status, $headers, $response) = Rack::runMiddleware(array());
    $this->assertEquals(200, $status);
    $this->assertEquals(array("Content-Type" => "text/html"), $headers);
    $this->assertEquals(array('Hello world!'), $response);
  }
  
  public function testRunMultipleMiddlewareShouldBeExecutedInReverseOrder()
  {
    Rack::useMiddleware("MyUppercaseMiddleware"); // Last
    Rack::useMiddleware("MyLowercaseMiddleware"); // First
    
    list($status, $headers, $response) = Rack::runMiddleware(array());
    $this->assertEquals(200, $status);
    $this->assertEquals(array("Content-Type" => "text/html"), $headers);
    $this->assertEquals(array('THIS IS IN LOWERCASE'), $response);
  }
}