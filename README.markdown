# rack-php

Rack-php is a PHP 5.3 framework that adheres to the rack interface from the ruby world (<http://rack.rubyforge.org/>).

It includes a simple php framework that lightly based around mvc.  Instead of using php's built in $_SERVER variables, various adaptors are used.

There is an adaptor for regular php web hosting (mod_php, mod_fcgid, etc...).  These are the current methods of serving php sites.  The adaptor will transform these environments into a rack request and load your rack-php app.

Also is an adaptor that bridges ruby and php allowing php rack apps to run as a rack endpoint to regular ruby rack servers.  This means you can run your php app using cool things like thin, mongrel, webrick, and passenger.

## Rackup Files


Here is a simple rackup.php file.  This is the php script bootstrapped by the various rack servers.


**public/index.php**

    <?
    require 'autoload.php'; // Set up class auto-loading

    $app = new SampleApplication();
    $env = \core\Rack::run($app);

This version is meant to be the index.php file used by traditional web hosts (mod_php, mod_fcgid, etc...)

**rackup.php**

    #!/usr/bin/env php
    <?
    require 'autoload.php'; // Set up class auto-loading

    $app = new SampleApplication();
    $env = \core\RubyBridge::run($app);

As you can see, this is a simple command line php script.  It creates an instance of our application and then runs it through the RubyBridge adaptor.

### Ruby server

This script in turn is called by the ruby config.ru file that follows:

**config.ru**

    require 'json'

    class RubyBridge
      def call(env)
        response = []
        data = JSON.dump(env)
        IO.popen("./rackup.php", 'r+') do |io|
          io.write data
          io.close_write
          response = io.read
        end
        JSON.load(response)
      end
    end

    run Cascade.new([
      File.new('public'),
      RubyBridge.new
    ])

RubyBridge is simple a proxy class that passes the real work to the php script via process pipes.

Here we can take advantage of the ruby Rack::File middle-ware to serve static files.  This is needed when running through servers like thin or mongrel directly.

## Simple Rack PHP App

Here is a small sample rack-php app that simply ignores all input and outputs the string "Hello World".

    class SampleApplication
    {
      public function call($env)
      {
        return array(200, array("Content-Type"=>"text/plain"), array("Hello World"));
      }
    }

As you can see, this works just like a ruby rack app.  The call function is passed the environment hash, and it must return an array of status, headers, body.

In ruby 1.9 and my php adaptor, the body needs to be iterable, so the body string needs to be wrapped in an array.

## TODO:

  * Implement file upload
  * Error Log Logging
  * Find ways to better follow the spec
  * Make a cool site using the framework
  * php middle-ware inside of a rack stack (not sure this is possible)
