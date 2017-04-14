<?php

namespace Emmanix2002\Notifier\Processor;

use Emmanix2002\Notifier\Message\MessageInterface;
use Emmanix2002\Notifier\Recipient\RecipientCollection;

class EmailValidationProcessor
{
    public function __invoke(MessageInterface $message, RecipientCollection $recipients)
    {
        $validRecipients = [];
        foreach ($recipients as $recipient) {
            $validated = filter_var($recipient->getAddress(), FILTER_VALIDATE_EMAIL);
            if ($validated === false) {
                continue;
            }
            $validRecipients[] = $validated;
        }
        return [$message, new RecipientCollection($validRecipients, $recipients->getRecipientClass())];
    }
}