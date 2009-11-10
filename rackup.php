#!/usr/bin/env php
<?
require realpath(dirname(__FILE__).'/lib/autoload.php');
use core\RubyBridge;

$app = new creationix\MyApplication;
RubyBridge::run($app);

