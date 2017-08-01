<?php

namespace Emmanix2002\Notifier\Handler;


use Emmanix2002\Notifier\Notifier;

abstract class AbstractHandler implements HandlerInterface
{
    /**
     * @inheritdoc
     */
    public function propagate(): bool
    {
        return false;
    }

    /**
     * Processes an exception for the handler
     *
     * @param \Throwable $e
     *
     * @return \Throwable
     */
    protected function processException(\Throwable $e)
    {
        Notifier::getLogger()->error($e->getMessage());
        return $e;
    }
}