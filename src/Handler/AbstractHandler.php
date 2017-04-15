<?php

namespace Emmanix2002\Notifier\Handler;

use Emmanix2002\Notifier\Message\MessageInterface;
use Emmanix2002\Notifier\Recipient\RecipientCollection;

abstract class AbstractHandler implements HandlerInterface
{
    
    /**
     * Whether or not to continue propagation
     *
     * @return bool
     */
    public function propagate(): bool
    {
        return false;
    }
}