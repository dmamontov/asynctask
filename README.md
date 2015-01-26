AsyncTask 1.0.0
===============

AsyncTask enables proper and easy use of the thread. This class allows to perform background operations and publish results on the thread without having to manipulate threads and/or handlers. [More information](https://dmamontov.github.io/asynctask).


## Requirements
* PHP version 5.3.3 or higher
* Module installed pcntl and posix
* All functions pcntl, posix and shm removed from the directive disable_functions

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
