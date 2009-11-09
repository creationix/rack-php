<?
namespace core;
use haml\HamlParser;

class ApplicationNotCallableException extends \Exception
{
  public function __construct()
  {
    parent::__construct();
    $this->message = 'Uncallable application called in ' . $this->getFile();
  }
}

class TooDeepApplicationCallException extends \Exception
{
  public function __construct()
  {
    parent::__construct();
    $this->message = 'Too many recursive calls in the app' . $this->getFile();
  }
}

abstract class App
{
  protected $status;
  protected $headers;
  protected $body;
  protected $env;
  
  public abstract function __invoke($env);

  protected function setup($env)
  {
    $this->status = 200;
    $this->headers = array("Content-Type"=>"text/html");
    $this->body = "";
    $this->env = $env;
  }
  
  protected function render($template, $vars)
  {
    $data = array(
      '__CONTENT__' => $this->render_partial($template, $vars),
      'title' => 'Rack PHP',
    );
    return $this->render_partial('application', $data);
  }

  protected function render_partial($template, $vars)
  {
    static $haml;
    if ($haml === null)
    {
      $haml = new HamlParser();
      $haml->setTmp(realpath(dirname(__FILE__) . "/../../tmp/cache"));
    }
    $filename = realpath(dirname(__FILE__)."/../../views/$template.haml");
    $haml->setFile($filename);
    $haml->append($vars);
    $haml->assign('env', $this->env);
    return $haml->render();
  }

  protected function write($text)
  {
    $this->body .= $text;
  }

  protected function header($key, $value)
  {
    $this->headers[$key] = $value;
  }
  
  protected function redirect($url, $status=301)
  {
    $this->headers["Location"] = $url;
    $this->status = $status;
  }

  protected function finish()
  {
    $this->headers['Content-Length'] = (string)strlen($this->body);
    return array($this->status, $this->headers, array($this->body));
  }
  
  protected function call($app, $env = null)
  {
    if ($this === $app) {
      throw new TooDeepApplicationCallException;
    }
    
    if (!is_callable($app)) {
      throw new ApplicationNotCallableException;
    }
    
    return $app($env);
  }
}
