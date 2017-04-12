<?php

namespace Emmanix2002\Notifier\Recipient;

class EmailRecipient implements RecipientInterface
{
    protected $email;
    
    public function __construct(string $email)
    {
        $this->email = $email;
    }
    
    /**
     * This returns the address for this recipient. This address can be anything:
     * email, phone number, array -- all that matters is that it can be used to identify a destination
     * for the handler
     *
     * @return string
     */
    public function getAddress()
    {
        return (string) $this->email;
    }
}