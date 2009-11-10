<?
namespace core;

class RubyBridge extends Rack
{
  // This converts the native PHP $_SERVER array into a rack hash and then removes the contents of
  // the $_SERVER variable.  This ensures loose coupling and allows for middleware and mock requests.
  protected static function get_env()
  {
    return json_decode(file_get_contents('php://stdin'), true);
  }
  
  protected static function execute($result, $env)
  {
    list($status, $headers, $body) = $result;
    $headers['X-Powered-By'] = 'rack-php ' . implode('.', $env['rack.version']);
    exit(json_encode(array($status, $headers, $body)));
  }

}
