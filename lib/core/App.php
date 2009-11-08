<?
namespace core;
use haml\HamlParser;

class App
{
  protected $status;
  protected $headers;
  protected $body;
  protected $env;
  protected $app = null;

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
      $haml->setTmp('/tmp');
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

}
