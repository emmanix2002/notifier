<?php

namespace Emmanix2002\Notifier\Message;

interface MessageInterface
{
    /**
     * Returns the content of the body of the message.
     *
     * @return string
     */
    public function getMessage();

    /**
     * Allows the message to be set.
     *
     * @param $message
     *
     * @return $this
     */
    public function setMessage($message);
}
