<?php

namespace Emmanix2002\Notifier\Processor;

use Emmanix2002\Notifier\Message\MessageInterface;
use Emmanix2002\Notifier\Recipient\RecipientCollection;

/**
 * This processor basically forces the message to have a maximum of 140 characters.
 */
class SmsMessageProcessor
{
    public function __invoke(MessageInterface $message, RecipientCollection $recipients)
    {
        $messageLength = strlen($message->getMessage());
        if ($messageLength > 140) {
            $message->setMessage(substr($message->getMessage(), 0, 137).'...');
        }

        return [$message, $recipients];
    }
}
