<?
namespace core;

class Rack
{
  private static $middleware = array();
  
  // This converts the native PHP $_SERVER array into a rack hash and then removes the contents of
  // the $_SERVER variable.  This ensures loose coupling and allows for middleware and mock requests.
  private static function get_env()
  {
    // This is modeled after the Rack standard <http://rack.rubyforge.org/doc/SPEC.html>
    $script_name = dirname($_SERVER['SCRIPT_NAME']);
    $full_info = str_replace($script_name, '', $_SERVER['REQUEST_URI']);
    $p = strpos($full_info, '?');
    if ($p === false) $p = strlen($full_info);
    $env = array(
      // The HTTP request method, such as “GET” or “POST”. This cannot ever be an empty string, and
      // so is always required.
      'REQUEST_METHOD' => $_SERVER['REQUEST_METHOD'],
      
      // The initial portion of the request URL’s “path” that corresponds to the application object,
      // so that the application knows its virtual “location”. This may be an empty string, if the
      // application corresponds to the “root” of the server. 
      'SCRIPT_NAME' => $script_name,
      
      // The remainder of the request URL’s “path”, designating the virtual “location” of the
      // request’s target within the application. This may be an empty string, if the request URL
      // targets the application root and does not have a trailing slash. This value may be
      // percent-encoded when I originating from a URL.
      'PATH_INFO' => substr($full_info, 0, $p),
      
      // The portion of the request URL that follows the ?, if any. May be empty, but is always required!
      'QUERY_STRING' => substr($full_info, $p+1),
      
      // When combined with SCRIPT_NAME and PATH_INFO, these variables can be used to complete the
      // URL. Note, however, that HTTP_HOST, if present, should be used in preference to SERVER_NAME
      // for reconstructing the request URL. SERVER_NAME and SERVER_PORT can never be empty strings,
      // and so are always required.
      'SERVER_NAME' => $_SERVER['SERVER_NAME'],
      'SERVER_PORT' => $_SERVER['SERVER_PORT'],
      
      // rack.version must be an array of Integers.
      'rack.version' => array(1,0),
      
      // rack.url_scheme must either be http or https.
      'rack.url_scheme' => (@$_SERVER['HTTPS'] ? 'https' : 'http'),

      // There must be a valid input stream in rack.input.
      'rack.input' => fopen('php://input', 'r'),

      // There must be a valid error stream in rack.errors.
      'rack.errors' => fopen('php://stderr', 'w'),

      'rack.multithread' => false,
      'rack.multiprocess' => false,
      'rack.run_once' => false,

    );

    // HTTP_ Variables:
    // Variables corresponding to the client-supplied HTTP request headers (i.e., variables whose
    // names begin with HTTP_). The presence or absence of these variables should correspond with
    // the presence or absence of the appropriate HTTP header in the request.
    //
    // Also include the rest just for fun and clear out $_SERVER.
    foreach(array_keys($_SERVER) as $key)
    {
      if (!array_key_exists($key, $env))
      {
        $env[$key] = $_SERVER[$key];
      }
      unset($_SERVER[$key]);
    }
    
    return $env;
  }
  
  public static function run($app)
  {
    $env =& static::get_env();
    ob_start();
    $result = $app->call($env);
    $output = ob_get_clean();
    if ($output) 
    {
      $result[1]["X-Output"] = json_encode($output);
    }
    static::execute($result, $env);
  }
  
  private static function execute($result, $env)
  {
    list($status, $headers, $body) = $result;
    fclose($env['rack.input']);
    fclose($env['rack.errors']);
    $headers['X-Powered-By'] = "rack-php ".implode('.',$env['rack.version']);
    $headers['Status'] = $status;
    foreach($headers as $key=>$value)
    {
      header("$key: $value");
    }
    foreach($body as $section)
    {
      print $section;
    }
    exit;
  }
  
  public static function useMiddleware($middleware)
  {
    array_push(self::$middleware, $middleware);
  }
  
  public static function middleware()
  {
    return self::$middleware;
  }
}
