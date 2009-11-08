<?php
require_once "TestHelper.php";
use core\Rack;

class RackTest extends PHPUnit_Framework_TestCase
{
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
    Rack::UseMiddleware($my_another_middleware);
    $this->assertEquals(2, count(Rack::middleware()));
    
    $middleware = Rack::middleware();
    $this->assertEquals($my_middleware, $middleware[0]);
    $this->assertEquals($my_another_middleware, $middleware[1]);
  }
}