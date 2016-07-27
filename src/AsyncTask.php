<?php
/**
 * AsyncTask
 *
 * Copyright (c) 2015, Dmitry Mamontov <d.slonyara@gmail.com>.
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 *   * Redistributions of source code must retain the above copyright
 *     notice, this list of conditions and the following disclaimer.
 *
 *   * Redistributions in binary form must reproduce the above copyright
 *     notice, this list of conditions and the following disclaimer in
 *     the documentation and/or other materials provided with the
 *     distribution.
 *
 *   * Neither the name of Dmitry Mamontov nor the names of his
 *     contributors may be used to endorse or promote products derived
 *     from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
 * FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 * COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
 * ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @package   asynctask
 * @author    Dmitry Mamontov <d.slonyara@gmail.com>
 * @copyright 2015 Dmitry Mamontov <d.slonyara@gmail.com>
 * @license   http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @since     File available since Release 1.0.5
 */

 /**
 * AsyncTask enables proper and easy use of the thread. This class allows to perform background operations and publish results on the thread without having to manipulate threads and/or handlers.
 *
 * @author    Dmitry Mamontov <d.slonyara@gmail.com>
 * @copyright 2015 Dmitry Mamontov <d.slonyara@gmail.com>
 * @license   http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @version   Release: 1.0.5
 * @link      https://github.com/dmamontov/asynctask
 * @since     Class available since Release 1.0.5
 * @todo      Planned to write a method publishProgress
 * @abstract
 */

abstract class AsyncTask
{
    /**
     * A numeric shared memory segment ID
     * @var integer
     * @static
     */
    private static $shmId;

    /**
     * The line number in which the object has been initialized
     * @var integer
     * @static
     */
    private $line;

    /**
     * The current class file
     * @var string
     */
    private $file;

    /**
     * Creates a new asynchronous task
     * @return void
     * @access public
     * @final
     */
    final public function __construct()
    {
        $error = '';
        if (version_compare(PHP_VERSION, '5.3.3', '<') || defined('HHVM_VERSION')) {
            $error .= "\n\e[0m\e[0;32mAsyncTask only officially supports PHP 5.3.3 and above,\e[0m";
        }
        if (!extension_loaded('pcntl')) {
            $error .= "\n\e[0m\e[0;32mAsyncTask uses the extension \"pcntl\",\e[0m";
        }
        if (!extension_loaded('posix')) {
            $error .= "\n\e[0m\e[0;32mAsyncTask uses the extension \"posix\",\e[0m";
        }

        if (empty($error) === false) {
            throw new RuntimeException(
               $error . "\n\e[0m\e[0;32myou will most likely encounter problems with non-installed extensions,"
                      . "\n\e[0;31mupgrading is strongly recommended.\e[0m\n"
           );
        }

        $line = debug_backtrace();
        $this->line = $line[0]['line'];

        $reflect =  new \ReflectionClass($this);
        $this->file =  $reflect->getFileName();

        self::$shmId = shm_attach((int) (ftok($this->file, 'A') . $this->line));
        shm_put_var(self::$shmId, 11511697116117115, 'PENDING');
        shm_put_var(self::$shmId, 112112105100, getmypid());
    }

    /**
     * Finish create an asynchronous task
     * @return void
     * @access public
     * @final
     */
    final public function __destruct()
    {
        if (
            @shm_has_var(self::$shmId, 112112105100) &&
            shm_get_var(self::$shmId, 112112105100) == getmypid()
        ) {
            shm_remove(self::$shmId);
        }
    }

    /**
     * Returns the variable with the given key
     * @param string $key
     * @return mixed
     * @access protected
     * @static
     * @final
     */
    final protected static function getProperty($key)
    {
        if (
            in_array($key, array('shmId', 'pid', 'ppid', 'status')) === false &&
            @shm_has_var(self::$shmId, self::getUid($key))
        ) {
            return shm_get_var(self::$shmId, self::getUid($key));
        } else {
            return false;
        }
    }

    /**
     * Inserts or updates a variable with the given key
     * @param string $key
     * @param string $value
     * @return boolean
     * @access protected
     * @static
     * @final
     */
    final protected static function setProperty($key, $value)
    {
        if (in_array($key, array('shmId', 'pid', 'ppid', 'status')) === false) {
            shm_put_var(self::$shmId, self::getUid($key), $value);
            return true;
        } else {
            return false;
        }
    }

    /**
     * Returns a unique integer identifier for a given key
     * @param string $key
     * @return integer
     * @access private
     * @static
     * @final
     */
    final private static function getUid($key)
    {
        $uid = '';
        for ($char = 0; $char < strlen($key); $char++) {
            $uid .= ord($key[ $char ]);
        }

        return (int) $uid;
    }

    /**
     * Executes the task with the specified parameters
     * @param mixed $parameters
     * @return void
     * @access public
     * @final
     */
    final public function execute($parameters)
    {
        pcntl_signal(SIGCHLD, SIG_IGN);
        $pid = pcntl_fork();
        if ($pid == -1) {
            exit();
        } elseif (!$pid) {
            self::$shmId = shm_attach((int) (ftok($this->file, 'A') . $this->line));
            shm_put_var(self::$shmId, 112105100, getmypid());
            shm_put_var(self::$shmId, 11511697116117115, 'RUNNING');

            $this->onPreExecute();

            $result = $this->doInBackground($parameters);

            $this->onPostExecute($result);

            if (@shm_has_var(self::$shmId, 112105100)) {
                shm_put_var(self::$shmId, 112105100, null);
                shm_put_var(self::$shmId, 11511697116117115, 'FINISHED');
            }
            exit();
        }
    }

    /**
     * Attempts to cancel execution of this task
     * @return boolean
     * @access public
     * @final
     */
    final public function cancel()
    {
        if (
            @shm_has_var(self::$shmId, 112105100) &&
            is_null(shm_get_var(self::$shmId, 112105100)) === false
        ) {
            $this->onCancelled();
            posix_kill(shm_get_var(self::$shmId, 112105100), SIGKILL);
            shm_put_var(self::$shmId, 112105100, null);
            shm_put_var(self::$shmId, 11511697116117115, 'CANCELED');
            return true;
        } else {
            return false;
        }
    }

    /**
     * Returns the current status of this task
     * @return string
     * @access public
     * @final
     */
    final public function getStatus()
    {
        return @shm_has_var(self::$shmId, 11511697116117115)
                   ? shm_get_var(self::$shmId, 11511697116117115)
                   : 'PENDING';
    }

    /**
     * Returns true if this task was cancelled before it completed normally
     * @return boolean
     * @access public
     * @final
     */
    final public function isCancelled()
    {
        return @shm_has_var(self::$shmId, 11511697116117115) == 'CANCELED' ? true : false;
    }

    /**
     * Runs on the thread before doInBackground($parameters)
     * @return void
     * @access protected
     */
    protected function onPreExecute()
    {
    }

    /**
     * Override this method to perform a computation on a background thread
     * @param mixed $parameters
     * @return mixed
     * @access protected
     * @abstract
     */
    abstract protected function doInBackground($parameters);

    /**
     * Runs on the thread after doInBackground($parameters)
     * @param mixed $result
     * @return void
     * @access protected
     */
    protected function onPostExecute($result)
    {
    }

    /**
     * Runs on the thread after cancel()
     * @return void
     * @access protected
     */
    protected function onCancelled()
    {
    }
}
