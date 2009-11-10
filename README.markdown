# rack-php

Rack-php is a PHP 5.3 framework that adheres to the rack interface from the ruby world (<http://rack.rubyforge.org/>).

It includes a simple php framework that lightly based around mvc.  Instead of using php's built in $_SERVER variables, various adaptors are used.

There is an adaptor for regular php web hosting (mod_php, mod_fcgid, etc...).  These are the current methods of serving php sites.  The adaptor will transform these environments into a rack request and load your rack-php app.

Also is an adaptor that bridges ruby and php allowing php rack apps to run as a rack endpoint to regular ruby rack servers.  This means you can run your php app using cool things like thin, mongrel, webrick, and passenger.

## Rackup Files


Here is a simple rackup.php file.  This is the php script bootstrapped by the various rack servers.


**public/index.php** <http://cloud.github.com/downloads/creationix/rack-php/apache.png>

    <?
    require 'autoload.php'; // Set up class auto-loading

    $app = new SampleApplication();
    $env = \core\Rack::run($app);

This version is meant to be the index.php file used by traditional web hosts (mod_php, mod_fcgid, etc...)

**rackup.php** <http://cloud.github.com/downloads/creationix/rack-php/thin.png>

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

We can call PHP middleware through a RubyMiddlewareBridge class:

**config.ru**
    
    class RubyMiddlewareBridge
      def initialize(app)
        @app = app
      end

      def call(env)
        env['rack.ruby_bridge_response'] = @app.call(env)

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

    use RubyMiddlewareBridge
    use Rack::Reloader
    use Rack::ContentLength

    app = proc do |env|
      [200, {'Content-Type' => 'text/html'}, ['Hello world']]
    end

    run app

And inside the rackup.php we can use PHP middleware to continue the rack app callchain:

**rackup.php**

    #!/usr/bin/env php
    <?
    require realpath(dirname(__FILE__).'/lib/autoload.php');
    use core\RubyBridge;

    class SwapHelloWorld extends core\App
    {
      protected $app;

      public function __construct($app)
      {
        $this->app = $app;
      }

      public function __invoke($env)
      {
        list($status, $headers, $body) = $this->call($this->app, $env);
        $parts = split(' ', $body[0]);
        $body = $parts[1] . ' ' . $parts[0];
        return array($status, $headers, array($body));
      }
    }

    RubyBridge::useMiddleware('SwapHelloWorld');
    RubyBridge::run(); // => world Hello

## Simple Rack PHP App

Here is a small sample rack-php app that simply ignores all input and outputs the string "Hello World".

    class SampleApplication extends App
    {
      public function __invoke($env)
      {
        return array(200, array("Content-Type"=>"text/plain"), array("Hello World"));
      }
    }

As you can see, this works just like a ruby rack app.  The __invoke magic function is passed the environment hash, and it must return an array of status, headers, body.

In ruby 1.9 and my php adaptor, the body needs to be iterable, so the body string needs to be wrapped in an array.

Here is an example how to use a Rack PHP App with middleware:

**public/index.php**

    <?php
    require realpath(dirname(__FILE__).'/../lib/autoload.php');
    use core\Rack;
    use core\App;

    class UpcaseMiddleware extends App
    {
      protected $app;

      public function __construct($app)
      {
        $this->app = $app;
      }

      public function __invoke($env)
      {
        list($status, $headers, $body) = $this->call($this->app, $env);

        $upcase_body = strtoupper($body[0]);
        return array($status, $headers, array($upcase_body));
      }
    }

    Rack::useMiddleware('UpcaseMiddleware');

    // We can also use the lambda function
    $app = function ($env) {
      return array(200, array('Content-Type' => 'text/html'), array('Hello World!'));
    };

    Rack::run($app); // => HELLO WORLD!

## TODO:

  * Implement file upload
  * Error Log Logging
  * Find ways to better follow the spec
  * Make a cool site using the framework
  * php middle-ware inside of a rack stack (not sure this is possible)
