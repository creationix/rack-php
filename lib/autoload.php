<?
// Set up class autoloader
function __autoload($class_name)
{
  $path = dirname(__FILE__).'/'.str_replace('\\','/', $class_name).'.php';
  if (is_file($path)) require $path;
}


