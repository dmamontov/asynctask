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

require 'AsyncTask.php';

class TestTask extends AsyncTask
{
    protected function onPreExecute()
    {
        self::setProperty("count", 100);
    }

    protected function doInBackground($parameters)
    {
        sleep(2);
        self::setProperty("count", self::getProperty("count") + $parameters[0]);
        sleep(1);
        return $parameters[1] . self::getProperty("count");
    }

    protected function onPostExecute($result)
    {
        echo "{$result}\n";
    }
}

$task = new TestTask();
$task->execute(array(100, 'task 1: '));
sleep(5);
$task2 = new TestTask();
$task2->execute(array(100, 'task 2: '));
echo "task 1: " . $task->getStatus() . "\n";
echo "task 2: " . $task2->getStatus() . "\n";
sleep(10);
if ($task->getStatus() == 'RUNNING') {
    $task->cancel();
}
if ($task2->getStatus() == 'RUNNING') {
    $task2->cancel();
}
echo "task 1: " . $task->getStatus() . "\n";
echo "task 2: " . $task2->getStatus() . "\n";
