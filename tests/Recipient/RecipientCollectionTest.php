<?php

namespace Emmanix2002\Notifier\Tests\Recipient;

use Emmanix2002\Notifier\Recipient\PhoneRecipient;
use Emmanix2002\Notifier\Recipient\RecipientCollection;
use PHPUnit\Framework\TestCase;

class RecipientCollectionTest extends TestCase
{
    /**
     * @var RecipientCollection
     */
    private $collection;
    
    public function setup()
    {
        $phones = range(1, 20);
        $this->collection = new RecipientCollection($phones, PhoneRecipient::class);
    }
    
    public function testNotEmpty()
    {
        $this->assertNotEmpty($this->collection);
    }
    
    public function testIterator()
    {
        $looped = 0;
        foreach ($this->collection as $recipient) {
            ++$looped;
        }
        $this->assertCount($looped, $this->collection);
    }
}