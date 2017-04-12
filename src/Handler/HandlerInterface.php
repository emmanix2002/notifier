<?php

namespace Emmanix2002\Notifier\Handler;

use Emmanix2002\Notifier\Message\MessageInterface;
use Emmanix2002\Notifier\Recipient\RecipientCollection;

interface HandlerInterface
{
    /**
     * Whether or not to continue propagation
     *
     * @return bool
     */
    public function propagate(): bool;
    
    /**
     * It performs an action on the received message; sending it to all the recipients
     *
     * @param MessageInterface    $message
     * @param RecipientCollection $recipients
     *
     * @return bool
     */
    public function handle(MessageInterface $message, RecipientCollection $recipients): bool;
}