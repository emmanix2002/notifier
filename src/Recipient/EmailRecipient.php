<?php

namespace Emmanix2002\Notifier\Recipient;

class EmailRecipient implements RecipientInterface
{
    /**
     * @var string
     */
    protected $email;
    
    /**
     * EmailRecipient constructor.
     *
     * @param string $email
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(string $email)
    {
        if (!self::validateAddress($email)) {
            throw new \InvalidArgumentException('An invalid email address was provided');
        }
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
    
    /**
     * Validates an email address
     *
     * @param string $email
     *
     * @return mixed
     */
    public static function validateAddress(string $email)
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }
}