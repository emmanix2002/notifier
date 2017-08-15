<?php

namespace Emmanix2002\Notifier\Tests\Recipient;

use Emmanix2002\Notifier\Recipient\PhoneRecipient;
use PHPUnit\Framework\TestCase;

class PhoneRecipientTest extends TestCase
{
    /**
     * @var PhoneRecipient
     */
    private $recipient;

    const PHONE = '2348123456789';

    public function setup()
    {
        $this->recipient = new PhoneRecipient(self::PHONE);
    }

    public function testGetAddress()
    {
        $this->assertEquals(self::PHONE, $this->recipient->getAddress());
    }
}
