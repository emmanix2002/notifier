<?php

namespace Emmanix2002\Notifier\Tests;

use Emmanix2002\Notifier\Channel\Channel;
use Emmanix2002\Notifier\Channel\ChannelInterface;
use Emmanix2002\Notifier\Handler\InfobipSmsHandler;
use Emmanix2002\Notifier\Notifier;
use Emmanix2002\Notifier\Processor\SmsMessageProcessor;
use PHPUnit\Framework\TestCase;

class NotifierTest extends TestCase
{
    const DEFAULT_CHANNEL = 'named_channel';

    /**
     * @var Notifier
     */
    private $notifier;

    public function setup()
    {
        $this->notifier = new Notifier(self::DEFAULT_CHANNEL, [
            new InfobipSmsHandler('username', 'password', '2348123456789'),
        ]);
        $this->notifier->pushProcessor(new SmsMessageProcessor());
    }

    public function testGetChannel()
    {
        $channel = $this->notifier->getChannel(self::DEFAULT_CHANNEL);
        $this->assertInstanceOf(ChannelInterface::class, $channel);
    }

    public function testGetChannels()
    {
        $this->assertNotEmpty($this->notifier->getChannels());
    }

    public function testAddChannel()
    {
        $channel = Channel::named('removed');
        $this->notifier->addChannel($channel);
        $this->assertCount(2, $this->notifier->getChannels());
    }

    /**
     * @depends testAddChannel
     */
    public function testRemoveChannel()
    {
        $this->notifier->removeChannel('removed');
        $this->assertCount(1, $this->notifier->getChannels());
    }

    public function testGetProcessors()
    {
        $this->assertNotEmpty($this->notifier->getProcessors());
    }

    public function testPushProcessor()
    {
        $this->notifier->pushProcessor(new SmsMessageProcessor());
        $this->assertCount(2, $this->notifier->getProcessors());
    }

    /**
     * @depends testPushProcessor
     */
    public function testPopProcessor()
    {
        $processor = $this->notifier->popProcessor();
        $this->assertInstanceOf(SmsMessageProcessor::class, $processor);
    }
}
