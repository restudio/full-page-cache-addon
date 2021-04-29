<?php

namespace ride\library\log\listener;

use ride\library\log\LogMessage;

use \PHPUnit_Framework_TestCase;

class FileLogListenerTest extends PHPUnit_Framework_TestCase {

    /**
     * @var string
     */
    protected $file;

    /**
     * @var ride\library\log\listener\FileLogListener
     */
    protected $listener;

    public function setUp() {
        $this->file = tempnam(sys_get_temp_dir(), 'log');
        $this->listener = new FileLogListener($this->file);
    }

    public function tearDown() {
        if (file_exists($this->file)) {
            unlink($this->file);
        }
    }

    /**
     * @dataProvider providerConstructThrowsExceptionWhenInvalidArgumentProvided
     * @expectedException ride\library\log\exception\LogException
     */
    public function testConstructThrowsExceptionWhenInvalidArgumentProvided($file, $truncateSize) {
        $listener = new FileLogListener($file);
        $listener->setFileTruncateSize($truncateSize);
    }

    public function providerConstructThrowsExceptionWhenInvalidArgumentProvided() {
        return array(
            array(array(), 0),
            array($this, 0),
            array('file', array()),
            array('file', $this),
            array('file', -500),
        );
    }

    public function testLogMessage() {
        $regex = "/id - ([0-9])* - 0\.123 - client - source   - ([0-9 ])* - I - title - description\\n/";

        $message = new LogMessage(LogMessage::LEVEL_INFORMATION, 'title', 'description', 'source');
        $message->setId('id');
        $message->setClient('client');
        $message->setMicrotime(0.123456789);

        $this->listener->logMessage($message);

        $this->assertRegExp($regex, file_get_contents($this->file));
    }

    public function testTruncate() {
        $message = new LogMessage(LogMessage::LEVEL_INFORMATION, 'title', 'description', 'source');

        $this->listener->setFileTruncateSize(1);

        for ($i = 1; $i <= 5; $i++) {
            $this->listener->logMessage($message);
            $this->listener->logMessage($message);
            $this->listener->logMessage($message);
            $this->listener->logMessage($message);

            switch ($i) {
                case 1:
                    $this->assertTrue(filesize($this->file) > 250);

                    break;
                case 2:
                    $this->assertTrue(filesize($this->file) > 500);

                    break;
                case 3:
                    $this->assertTrue(filesize($this->file) > 750);

                    break;
                case 4:
                    $this->assertTrue(filesize($this->file) < 250);

                    break;
            }
        }

        $size = 0;
        $this->listener->setFileTruncateSize($size);

        file_put_contents($this->file, '');
        clearstatcache(null, $this->file);

        $this->assertEquals($size, filesize($this->file));

        for ($i = 1; $i <= 25; $i++) {
            $this->listener->logMessage($message);
            $this->listener->logMessage($message);
            $this->listener->logMessage($message);
            $this->listener->logMessage($message);
            $this->listener->logMessage($message);
            $this->listener->logMessage($message);
            $this->listener->logMessage($message);
            $this->listener->logMessage($message);

            clearstatcache(null, $this->file);
            $newSize = filesize($this->file);

            $this->assertGreaterThan($size, $newSize);

            $size = $newSize;
        }
    }

    public function testInvalidFileDoesNotThrowException() {
        $listener = new FileLogListener('/invalid.file');
        $listener->logMessage(new LogMessage(LogMessage::LEVEL_ERROR, 'title'));

        $this->assertTrue(true);
    }

}