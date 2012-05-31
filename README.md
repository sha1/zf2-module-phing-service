# PhingService

## Introduction
PhingService is module for Zend Framework 2 that will enable you to run
[phing](http://www.phing.info/ "Phing") build files from within ZF2 projects.

## Requirements
  * Zend Framework 2 (https://github.com/zendframework/zf2)
  * Phing
  * The ability to run php from the commandline [exec](php.net/manual/en/function.exec.php)

## Release information

THIS RELEASE IS A DEVELOPMENT RELEASE AND NOT INTENDED FOR PRODUCTION USE.
PLEASE USE AT YOUR OWN RISK.

  * v1.0.0-beta1 - initial release
  * v1.0.0-beta2 - 'composerized', updated to zf2-beta-4

## Installation
### Using Composer (recommended)
The recommended way to get a working copy of this project is to modify your composer.json
in your project root. This will take care of dependancies.

    "require":{
        "bushbaby/zf2-module-phing-service":"1.0.*",
     },

and then update

	cd /to/your/project/directory
    php composer.phar update
    
## Configuration

  * Open `.../configs/application.config.php` and add 'PhingService'
    to the 'modules' parameter to register the module within your application.
  * Optionally copy `.../vendor/bushbaby/zf2-module-phing-service/config/module.phingservice.global.php.dist` to
     `.../config/autoload/module.phingservice.global.php` to override some defaults.

## How to use PhingService
There is only one command to use which is `$Service->build($target, $phingOptions);`. The
build method returns an associative array that contains the return status of the phing
executable, the command has been issued and any output generated by phing (Both stdout and
stderr are captured).

### Controller example
You can create an instance of the Service manually, however it is recommended to retrieve an
configured instance from the ServiceLocator. The ServiceLocator is available in
every controller so retrieval is trivial.

    public function indexAction() {
        $options = array('buildFile' => __DIR__ . '/../../../data/build-example.xml');

        $buildResult = $this->getServiceLocator()->get('PhingService')->build('show-defaults dist', $options);

        if ($buildResult['returnStatus'] > 0) {
      	    // problem
            echo $buildResult['command'];
            echo $buildResult['output'];
        } else {
            // yeah
            echo $buildResult['output'];
        }

        $view = new ViewModel($buildResult);
        $view->setTemplate('phingservice/index');

        return $view;
    }

To get a quick taste you can enable the defined route in module.conf.php and point your 
browser at `http://localhost/phingservice` to get an working example.

## License
The MIT License (MIT)
Copyright (c) 2012 bushbaby multimedia

Permission is hereby granted, free of charge, to any person obtaining a copy of this
software and associated documentation files (the "Software"), to deal in the Software
without restriction, including without limitation the rights to use, copy, modify, merge,
publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons
to whom the Software is furnished to do so, subject to the following conditions:
The above copyright notice and this permission notice shall be included in all copies or
substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED,
INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR
PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE
FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR
OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
OTHER DEALINGS IN THE SOFTWARE.

_Phing has it's own licence [Phing licence](http://www.phing.info/trac/wiki/Users/License/ "Phing licence")_