<?php

namespace Emmanix2002\Notifier\Tests\Channel;

use Emmanix2002\Notifier\Channel\Channel;
use Emmanix2002\Notifier\Channel\ChannelInterface;
use Emmanix2002\Notifier\Handler\SendgridEmailHandler;
use Emmanix2002\Notifier\Processor\EmailValidationProcessor;
use Emmanix2002\Notifier\Processor\SmsMessageProcessor;
use PHPUnit\Framework\TestCase;

class ChannelTest extends TestCase
{
    /**
     * @var Channel
     */
    private $channel;
    
    public function setup()
    {
        $this->channel = Channel::named('named');
        $this->channel->pushHandler(new SendgridEmailHandler('fake-key'));
        $this->channel->pushProcessor(new EmailValidationProcessor());
    }
    
    public function testNamed()
    {
        $channel = Channel::named('named');
        $this->assertInstanceOf(ChannelInterface::class, $channel);
    }
    
    public function testPushHandler()
    {
        $this->assertNotEmpty($this->channel->getHandlers());
    }
    
    public function testGetProcessors()
    {
        $this->assertNotEmpty($this->channel->getProcessors());
    }
    
    public function testPushProcessor()
    {
        $this->channel->pushProcessor(new SmsMessageProcessor());
        $this->assertCount(2, $this->channel->getProcessors());
    }
    
    
    public function testPopProcessor()
    {
        $this->channel->pushProcessor(new SmsMessageProcessor());
        $processor = $this->channel->popProcessor();
        $this->assertInstanceOf(SmsMessageProcessor::class, $processor);
    }
}