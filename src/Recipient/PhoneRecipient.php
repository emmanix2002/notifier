<?php

namespace Emmanix2002\Notifier\Recipient;

class PhoneRecipient implements RecipientInterface
{
    protected $phone;
    
    public function __construct(string $phone)
    {
        $this->phone = (string) $phone;
    }
    
    /**
     * This returns the address for this recipient. This address can be anything:
     * email, phone number, array -- all that matters is that it can be used to identify a destination
     * for the handler
     *
     * @return mixed
     */
    public function getAddress()
    {
        return (string) $this->phone;
    }
}