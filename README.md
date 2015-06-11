[![Build Status](https://travis-ci.org/dmamontov/asynctask.svg?branch=master)](https://travis-ci.org/dmamontov/asynctask)
[![Latest Stable Version](https://poser.pugx.org/dmamontov/asynctask/v/stable.svg)](https://packagist.org/packages/dmamontov/asynctask)
[![License](https://poser.pugx.org/dmamontov/asynctask/license.svg)](https://packagist.org/packages/dmamontov/asynctask)
[![Total Downloads](https://poser.pugx.org/dmamontov/asynctask/downloads)](https://packagist.org/packages/dmamontov/asynctask)

AsyncTask
=========

AsyncTask enables proper and easy use of the thread. This class allows to perform background operations and publish results on the thread without having to manipulate threads and/or handlers. [More information](https://dmamontov.github.io/asynctask).


## Requirements
* PHP version ~5.3.3
* Module installed pcntl and posix
* All functions pcntl, posix and shm removed from the directive disable_functions

## Installation

1) Install [composer](https://getcomposer.org/download/)

2) Follow in the project folder:
```bash
composer require dmamontov/asynctask ~1.0.5
```

In config `composer.json` your project will be added to the library `dmamontov/asynctask`, who settled in the folder `vendor/`. In the absence of a config file or folder with vendors they will be created.

If before your project is not used `composer`, connect the startup file vendors. To do this, enter the code in the project:
```php
require 'path/to/vendor/autoload.php';
```

### Example of work
```php
class TestTask extends AsyncTask
{
    protected function onPreExecute()
    {
    }

    protected function doInBackground($parameters)
    {
        return $parameters;
    }

    protected function onPostExecute($result)
    {
        echo $result;
    }
}

$task = new TestTask();
$task->execute('test');
```
