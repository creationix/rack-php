#!/usr/bin/env php
<?
require realpath(dirname(__FILE__).'/lib/autoload.php');

$app = new \creationix\MyApplication();
$env = \core\RubyBridge::run($app);

