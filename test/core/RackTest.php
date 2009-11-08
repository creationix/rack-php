<?php
require_once "TestHelper.php";
use core\Rack;

class RackTest extends PHPUnit_Framework_TestCase
{
  public function testMiddlewareReturnsEmptyArray()
  {
    $this->assertEquals(array(), Rack::middleware());
  }
}