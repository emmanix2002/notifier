<?php

namespace Emmanix2002\Notifier\Handler;


abstract class AbstractHandler implements HandlerInterface
{
    /**
     * @inheritdoc
     */
    public function propagate(): bool
    {
        return false;
    }
}