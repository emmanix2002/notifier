<?php

namespace Emmanix2002\Notifier\Tests\Recipient;

use Emmanix2002\Notifier\Recipient\EmailRecipient;
use PHPUnit\Framework\TestCase;

class EmailRecipientTest extends TestCase
{
    /**
     * @var EmailRecipient
     */
    private $recipient;
    
    public function setup()
    {
        $this->recipient = new EmailRecipient('id@domain.com');
    }
    
    public function testGetAddress()
    {
        $this->assertEquals('id@domain.com', $this->recipient->getAddress());
    }
    
    public function testSetCc()
    {
        $this->recipient->setCc(['id1@domain.com', 'id2@domain.com']);
        $this->assertNotEmpty($this->recipient->getCc());
    }
    
    public function testSetBcc()
    {
        $this->recipient->setBcc(['id3@domain.com', 'id4@domain.com']);
        $this->assertNotEmpty($this->recipient->getBcc());
    }
    
    public function testValidateEmails()
    {
        $addresses = ['id3@domain.com', 'id4@domain.com', 'fake email', 'failed validation'];
        $filtered = array_filter($addresses, [EmailRecipient::class, 'validateAddress']);
        $this->assertCount(2, $filtered);
    }
    
    public function testAddCc()
    {
        $count = count($this->recipient->getCc());
        $this->recipient->addCc('id6@domain.com');
        $this->assertCount(++$count, $this->recipient->getCc());
    }
    
    public function testAddBcc()
    {
        $count = count($this->recipient->getBcc());
        $this->recipient->addBcc('id8@domain.com');
        $this->assertCount(++$count, $this->recipient->getBcc());
    }
}