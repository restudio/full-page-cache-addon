<?php

namespace ride\library\log;

use ride\library\log\listener\LogListener;
use ride\library\Timer;

use \Exception;
use \PHPUnit_Framework_TestCase;

class LogTest extends PHPUnit_Framework_TestCase {

    protected $log;

    protected function setUp() {
        $this->log = new Log();
    }

    public function testConstruct() {
        $id = 'id';
        $client = 'client';
        $timer = new Timer();

        $log = new Log($id, $client, $timer);

        $this->assertEquals($id, $log->getId());
        $this->assertEquals($client, $log->getClient());
    }

    public function testGetIdGeneratesStringWhenNotSet() {
        $log = new Log();

        $id = $log->getId();

        $this->assertNotEmpty($id);
    }

    public function testGetTime() {
        time_nanosleep(0, 300000000);
        $result = $this->log->getTime();
        $this->assertTrue(0.300 <= $result && $result <= 0.302);
    }

    public function testAddLogListener() {
        $listeners = $this->log->getLogListeners();
        $this->assertTrue(count($listeners) == 0);

        $listener1 = new TestLogListener();
        $listener2 = new TestLogListener();

        $this->log->addLogListener($listener1);

        $listeners = $this->log->getLogListeners();
        $this->assertTrue(count($listeners) == 1);
        $this->assertTrue(in_array($listener1, $listeners));

        $this->log->addLogListener($listener2);

        $listeners = $this->log->getLogListeners();
        $this->assertTrue(count($listeners) == 2);
        $this->assertTrue(in_array($listener1, $listeners));
        $this->assertTrue(in_array($listener2, $listeners));
    }

    public function testRemoveLogListener() {
        $listener1 = new TestLogListener();
        $listener2 = new TestLogListener();

        $listeners = $this->log->getLogListeners();
        $this->assertTrue(count($listeners) == 0);

        $this->log->addLogListener($listener1);

        $listeners = $this->log->getLogListeners();
        $this->assertTrue(count($listeners) == 1);

        $this->log->addLogListener($listener2);

        $listeners = $this->log->getLogListeners();
        $this->assertTrue(count($listeners) == 2);

        $this->log->removeLogListener($listener2);

        $listeners = $this->log->getLogListeners();
        $this->assertTrue(count($listeners) == 1);

        $this->log->removeLogListener($listener1);

        $listeners = $this->log->getLogListeners();
        $this->assertTrue(count($listeners) == 0);
    }

    public function testLogMessage() {
        $listener = new TestLogListener();
        $id = 'test-id';
        $client = 'test-client';
        $title = 'Test log title';
        $description = 'Test log description';
        $source = 'test';

        $message = new LogMessage(LogMessage::LEVEL_ERROR, $title, $description, $source);

        $this->log->setId($id);
        $this->log->setClient($client);
        $this->log->addLogListener($listener);

        $this->log->logMessage($message);

        $this->assertTrue($listener->message == $message, 'Item not added');
        $this->assertTrue($listener->message->getTitle() == $title, 'Title is not the initial title');
        $this->assertTrue($listener->message->getDescription() == $description, 'Description is not the initial description');
        $this->assertTrue($listener->message->getLevel() == LogMessage::LEVEL_ERROR, 'Level is not the initial level');
        $this->assertTrue($listener->message->getSource() == $source, 'Source is not the initial source');
        $this->assertTrue($listener->message->getId() == $id, 'Id is not set');
        $this->assertTrue($listener->message->getClient() == $client, 'Client is not set');
        $this->assertNotNull($listener->message->getMicrotime(), 'Microtime is empty');
        $listener->message = null;

        $this->log->logDebug($title, $description);

        $this->assertNotNull($listener->message, 'Item not added');
        $this->assertTrue($listener->message->getTitle() == $title, 'Title is not the initial title');
        $this->assertTrue($listener->message->getDescription() == $description, 'Description is not the initial description');
        $this->assertTrue($listener->message->getLevel() == LogMessage::LEVEL_DEBUG, 'Level is not the initial level');
        $listener->message = null;

        $this->log->logInformation($title, $description);

        $this->assertNotNull($listener->message, 'Item not added');
        $this->assertTrue($listener->message->getTitle() == $title, 'Title is not the initial title');
        $this->assertTrue($listener->message->getDescription() == $description, 'Description is not the initial description');
        $this->assertTrue($listener->message->getLevel() == LogMessage::LEVEL_INFORMATION, 'Level is not the initial level');
        $listener->message = null;

        $this->log->logWarning($title, $description);

        $this->assertNotNull($listener->message, 'Item not added');
        $this->assertTrue($listener->message->getTitle() == $title, 'Title is not the initial title');
        $this->assertTrue($listener->message->getDescription() == $description, 'Description is not the initial description');
        $this->assertTrue($listener->message->getLevel() == LogMessage::LEVEL_WARNING, 'Level is not the initial level');
        $listener->message = null;

        $this->log->logError($title, $description);

        $this->assertNotNull($listener->message, 'Item not added');
        $this->assertTrue($listener->message->getTitle() == $title, 'Title is not the initial title');
        $this->assertTrue($listener->message->getDescription() == $description, 'Description is not the initial description');
        $this->assertTrue($listener->message->getLevel() == LogMessage::LEVEL_ERROR, 'Level is not the initial level');
        $listener->message = null;

        $exception1 = new Exception('Subexception');
        $exception2 = new Exception('Exception', 10, $exception1);

        $this->log->logException($exception2);

        $this->assertNotNull($listener->message, 'Item not added');
        $this->assertEquals('Exception: ' . $exception1->getMessage(), $listener->message->getTitle());
        $this->assertEquals($exception1->getTraceAsString(), $listener->message->getDescription());
        $listener->message = null;
    }

}

class TestLogListener implements LogListener {

    public $message;

    public function logMessage(LogMessage $message) {
        $this->message = $message;
    }

}