<?php

namespace ride\library\log;

use \PHPUnit_Framework_TestCase;

class LogMessageTest extends PHPUnit_Framework_TestCase {

    public function __toString() {
        return get_class($this);
    }

    public function testConstruct() {
        $level = LogMessage::LEVEL_INFORMATION;
        $title = 'title';
        $description = 'description';
        $source = 'source';

        $logMessage = new LogMessage($level, $title, $description, $source);

        $this->assertEquals($level, $logMessage->getLevel());
        $this->assertEquals($title, $logMessage->getTitle());
        $this->assertEquals($description, $logMessage->getDescription());
        $this->assertEquals($source, $logMessage->getSource());
        $this->assertNotNull($logMessage->getDate());
        $this->assertNull($logMessage->getClient());
        $this->assertNull($logMessage->getMicrotime());
    }

    /**
     * @dataProvider providerConstructWithInvalidParametersThrowsException
     * @expectedException ride\library\log\exception\LogException
     */
    public function testConstructWithInvalidParametersThrowsException($level, $title, $description, $source, $id) {
        $logMessage = new LogMessage($level, $title, $description, $source);
        $logMessage->setId($id);
    }

    public function providerConstructWithInvalidParametersThrowsException() {
        return array(
            array(null, null, null, null, null),
            array('test', 'test', null, null, null),
            array(1, null, null, null, null),
            array(1, 'title', array(), null, null),
            array(1, 'title', new \StdClass(), null, null),
        );
    }

    public function testSetDescriptionWithObject() {
        $expected = (string) $this;

        $logMessage = new LogMessage(LogMessage::LEVEL_DEBUG, 'title', $this);
        $this->assertEquals($expected, $logMessage->getDescription());
    }

    public function testSetId() {
        $id = 'id';

        $logMessage = new LogMessage(LogMessage::LEVEL_DEBUG, 'title');
        $this->assertNull($logMessage->getId());

        $logMessage->setId(null);
        $this->assertNull($logMessage->getId());

        $logMessage->setId($id);
        $this->assertEquals($id, $logMessage->getId());
    }

    /**
     * @dataProvider providerSetIdThrowsExceptionWhenInvalidValueProvided
     * @expectedException ride\library\log\exception\LogException
     */
    public function testSetIdThrowsExceptionWhenInvalidValueProvided($id) {
        $logMessage = new LogMessage(LogMessage::LEVEL_DEBUG, 'title');
        $logMessage->setId($id);
    }

    public function providerSetIdThrowsExceptionWhenInvalidValueProvided() {
        return array(
        	array(array()),
        	array(new \StdClass()),
        );
    }

    public function testLevel() {
        $logMessage = new LogMessage(LogMessage::LEVEL_DEBUG, 'title');
        $this->assertTrue($logMessage->isDebug());
        $this->assertFalse($logMessage->isInformation());
        $this->assertFalse($logMessage->isWarning());
        $this->assertFalse($logMessage->isError());

        $logMessage = new LogMessage(LogMessage::LEVEL_ERROR, 'title');
        $this->assertFalse($logMessage->isDebug());
        $this->assertFalse($logMessage->isInformation());
        $this->assertFalse($logMessage->isWarning());
        $this->assertTrue($logMessage->isError());

        $logMessage = new LogMessage(LogMessage::LEVEL_WARNING, 'title');
        $this->assertFalse($logMessage->isDebug());
        $this->assertFalse($logMessage->isInformation());
        $this->assertTrue($logMessage->isWarning());
        $this->assertFalse($logMessage->isError());

        $logMessage = new LogMessage(LogMessage::LEVEL_INFORMATION, 'title');
        $this->assertFalse($logMessage->isDebug());
        $this->assertTrue($logMessage->isInformation());
        $this->assertFalse($logMessage->isWarning());
        $this->assertfalse($logMessage->isError());
    }

}
