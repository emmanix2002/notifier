<?php

namespace Emmanix2002\Notifier\Message;

class SmsMessage implements MessageInterface
{
    /**
     * @var string
     */
    protected $message;
    
    public function __construct(string $message = null)
    {
        $this->message = $message;
    }
    
    /**
     * Sets the message
     *
     * @param string $message
     *
     * @return $this
     */
    public function setMessage($message)
    {
        $this->message = (string) $message;
        return $this;
    }
    
    /**
     * Returns the content of the body of the message
     *
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }
}