<?php

namespace Emmanix2002\Notifier\Tests\Recipient;

use Emmanix2002\Notifier\Recipient\SendgridEmailRecipient;
use PHPUnit\Framework\TestCase;

class SendgridEmailRecipientTest extends TestCase
{
    /**
     * @var SendgridEmailRecipient
     */
    private $recipient;
    
    public function setup()
    {
        $this->recipient = new SendgridEmailRecipient('id@example.com');
    }
    
    public function testSetSubstitutions()
    {
        $this->recipient->setSubstitutions(['field' => 'value', 'field1' => 'value1']);
        $this->assertNotEmpty($this->recipient->getSubstitutions());
    }
    
    public function testAddSubstitution()
    {
        $this->recipient->addSubstitution('field2', 'value2', true);
        $this->assertEquals('value2', $this->recipient->getSubstitutions()['field2']);
    }
}