<?php

namespace ride\library\log\listener;

use ride\library\log\LogMessage;

use \PHPUnit_Framework_TestCase;

class AbstractLogListenerTest extends PHPUnit_Framework_TestCase {

    public function testLogMessage() {
        $message = new LogMessage(LogMessage::LEVEL_INFORMATION, 'title', 'description', 'source');

        $listener = $this->getMock('ride\\library\\log\\listener\\AbstractLogListener', array('log'));
        $listener->expects($this->once())->method('log')->with($this->equalTo($message));

        $listener->logMessage($message);

        $listener->setLevel(LogMessage::LEVEL_ERROR);

        $listener->logMessage($message);

        $this->assertEquals(LogMessage::LEVEL_ERROR, $listener->getLevel());
    }

}