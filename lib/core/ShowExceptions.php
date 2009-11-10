<?php
namespace core;

class ShowExceptions extends App
{
  protected $app;
  
  public function __construct($app)
  {
    $this->app = $app;
  }
  
  public function __invoke($env)
  {
    try {
      return $this->call($this->app, $env);
    } catch (\Exception $ex) {
      $body = $this->handleException($env, $ex);
      return array(500, array('Content-Type' => 'text/html'), $body);
    }
  }
  
  private function handleException($env, $ex)
  {
    fwrite($env['rack.errors'], $ex->__toString());
    return array($ex->__toString());
  }
}
