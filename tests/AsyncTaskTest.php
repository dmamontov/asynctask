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
 * AsyncTaskTes - test class
 *
 * @author    Dmitry Mamontov <d.slonyara@gmail.com>
 * @copyright 2015 Dmitry Mamontov <d.slonyara@gmail.com>
 * @license   http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @version   Release: 1.0.5
 * @link      https://github.com/dmamontov/asynctask
 * @since     Class available since Release 1.0.5
 */
 
class AsyncTaskTest extends PHPUnit_Framework_TestCase
{
    public function testStatusess()
    {
        $task = new AsyncTaskInstance();

        $this->assertEquals('PENDING', $task->getStatus());
        $task->execute(null);
        sleep(1);
        $this->assertEquals('RUNNING', $task->getStatus());
        sleep(2);
        $this->assertEquals('FINISHED', $task->getStatus());
        $task->__destruct();
    }

    public function testCanceled()
    {
        $task = new AsyncTaskInstance();
        $this->assertFileExists(realpath(__DIR__ . '/../src/AsyncTask.php'));
        if (file_exists(realpath(__DIR__ . '/../src/AsyncTask.php'))) {
            $shmId = shm_attach((int) (ftok(realpath(__DIR__ . '/../src/AsyncTask.php'), 'A') . 72));

            $task->execute(null);
            sleep(1);
            $task->cancel();
            sleep(1);


            $this->assertTrue(@shm_has_var($shmId, 112105100));
            $this->assertNull(@shm_get_var($shmId, 112105100));

            $this->assertEquals('CANCELED', $task->getStatus());
            $this->assertTrue(@shm_has_var($shmId, 116101115116));
            $this->assertTrue(@shm_get_var($shmId, 116101115116));
            $this->assertTrue($task->isCancelled());
        }
        $task->__destruct();
    }

    public function testSetProperty()
    {
        $task = new AsyncTaskInstance();
        $this->assertFileExists(realpath(__DIR__ . '/../src/AsyncTask.php'));
        if (file_exists(realpath(__DIR__ . '/../src/AsyncTask.php'))) {
            $shmId = shm_attach((int) (ftok(realpath(__DIR__ . '/../src/AsyncTask.php'), 'A') . 96));

            $task->execute('testProperty');
            sleep(1);
            $this->assertTrue(@shm_has_var($shmId, 116101115116));
            $this->assertEquals('testProperty', @shm_get_var($shmId, 116101115116));
            sleep(1);
        }
        $task->__destruct();
    }

    public function testPreAndPostExecute()
    {
        $task = new AsyncTaskInstance();
        $this->assertFileExists(realpath(__DIR__ . '/../src/AsyncTask.php'));
        if (file_exists(realpath(__DIR__ . '/../src/AsyncTask.php'))) {
            $shmId = shm_attach((int) (ftok(realpath(__DIR__ . '/../src/AsyncTask.php'), 'A') . 112));

            $task->execute(100);
            sleep(1);
            $this->assertTrue(@shm_has_var($shmId, 116101115116));
            $this->assertEquals(200, @shm_get_var($shmId, 116101115116));
            sleep(2);
            $this->assertEquals(300, @shm_get_var($shmId, 116101115116));
        }
        $task->__destruct();
    }

    public function testMultiInstance()
    {
        $this->assertFileExists(realpath(__DIR__ . '/../src/AsyncTask.php'));
        if (file_exists(realpath(__DIR__ . '/../src/AsyncTask.php'))) {
            $taskOne = new AsyncTaskInstance();
            $tokOne = (int) (ftok(realpath(__DIR__ . '/../src/AsyncTask.php'), 'A') . 131);

            $taskTwo = new AsyncTaskInstance();
            $tokTwo = (int) (ftok(realpath(__DIR__ . '/../src/AsyncTask.php'), 'A') . 134);

            $this->assertNotEquals($tokOne, $tokTwo);

            $taskOne->execute(100);
            $taskTwo->execute(200);
            sleep(3);
            $shmIdOne = shm_attach($tokOne);
            $shmIdTwo = shm_attach($tokTwo);

            $this->assertTrue(@shm_has_var($shmIdOne, 116101115116));
            $this->assertTrue(@shm_has_var($shmIdTwo, 116101115116));

            $this->assertEquals(300, @shm_get_var($shmIdOne, 116101115116));
            $this->assertEquals(400, @shm_get_var($shmIdTwo, 116101115116));

            $this->assertNotEquals(@shm_get_var($shmIdOne, 116101115116), @shm_get_var($shmIdTwo, 116101115116));
        }
        $taskOne->__destruct();
        $taskTwo->__destruct();
    }
}
