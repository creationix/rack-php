<?
namespace creationix;
use core\App;

class MyApplication extends App
{
  
  public function call($env)
  {
    $this->setup($env);
    
    $this->write($this->render('config', array('config' => $env)));
      
    return $this->finish();
  }
    
}

