<?php

namespace ride\library\decorator;

use ride\library\decorator\DateFormatDecorator;
use ride\library\decorator\StorageSizeDecorator;
use ride\library\log\LogMessage;

use \PHPUnit_Framework_TestCase;

class LogMessageDecoratorTest extends PHPUnit_Framework_TestCase {

    public function testDecorate() {
        $decorator = new LogMessageDecorator();

        $value = 'A non log message instance';

        $result = $decorator->decorate($value);

        $this->assertEquals($value, $result);

        $message = new LogMessage(LogMessage::LEVEL_INFORMATION, 'title', 'description', 'source');
        $message->setId('id');
        $message->setClient('client');
        $message->setMicrotime(0.123456789);

        $result = $decorator->decorate($message);

        $regex = "/id - ([0-9])* - 0\.123 - client - source   - ([0-9 ])* - I - title - description\\n/";
        $this->assertRegExp($regex, $result);
    }

    public function testSubDecorators() {
        $message = new LogMessage(LogMessage::LEVEL_INFORMATION, 'title', 'description', 'source');
        $message->setId('id');
        $message->setClient('client');
        $message->setMicrotime(0.123456789);

        $dateDecorator = new DateFormatDecorator();
        $dateDecorator->setDateFormat('Y-m-d');
        $memoryDecorator = new StorageSizeDecorator();

        $decorator = new LogMessageDecorator();
        $decorator->setDateDecorator($dateDecorator);
        $decorator->setMemoryDecorator($memoryDecorator);
        $result = $decorator->decorate($message);

        $regex = "/id - " . date('Y-m-d') . " - 0\.123 - client - source   - ([0-9 .])*Mb - I - title - description\\n/";
        $this->assertRegExp($regex, $result);

        $this->assertEquals($dateDecorator, $decorator->getDateDecorator());
        $this->assertEquals($memoryDecorator, $decorator->getMemoryDecorator());
    }

}