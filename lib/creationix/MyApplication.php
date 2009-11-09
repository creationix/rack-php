<?
namespace creationix;
use core\App;

class MyApplication extends App
{
  public function __invoke($env)
  {
    $this->setup($env);
    $this->write($this->render('config', array('config' => $env)));
    return $this->finish();
  }
}

