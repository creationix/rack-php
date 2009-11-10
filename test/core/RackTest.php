<?php
require_once "TestHelper.php";
use core\Rack;
use core\App;

class RackTest extends PHPUnit_Framework_TestCase
{
  protected $app;
  
  public function setUp()
  {
    $this->app = new MockApp;
  }
  
  public function tearDown()
  {
    Rack::clearMiddleware();
  }
  
  public function testMiddlewareReturnsEmptyArray()
  {
    $this->assertEquals(array(), Rack::middleware());
  }
  
  public function testShouldRunApp()
  {
    $env = MockRack::run($this->app);
    $this->assertEquals(200, $env[0]);
    $this->assertEquals('text/html', $env[1]['Content-Type']);
    $this->assertEquals(array('Hello World'), $env[2]);
  }
  
  public function testShouldRunLambdaApp()
  {
    $app = function ($env) {
      return array(200, array('Content-Type' => 'text/plain'), array('This is from lambda!'));
    };
    
    $env = MockRack::run($app);
    $this->assertEquals(200, $env[0]);
    $this->assertEquals('text/plain', $env[1]['Content-Type']);
    $this->assertEquals(array('This is from lambda!'), $env[2]);
  }
}