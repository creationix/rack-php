<?
namespace creationix;
use core\App;

class MyApplication extends App
{
  
  public function call($env)
  {
    $this->setup($env);
    
    $this->write($this->render('config', array('config'=>$env)));
#    $this->write("Hello World\n");
#    ksort($env);
#    $this->write(var_export($env, true));
#    $this->header("Content-Type", "text/plain");
    
    return $this->finish();
  }
    
}

