<?php

namespace Emmanix2002\Notifier;

use Emmanix2002\Notifier\Message\MessageInterface;
use Emmanix2002\Notifier\Recipient\RecipientCollection;

trait ManagesProcessors
{
    /**
     * Pushes a new processor onto the stack.
     *
     * @param callable $processor
     *
     * @return $this
     */
    public function pushProcessor(callable $processor)
    {
        if (empty($this->processors)) {
            $this->processors = [];
        }
        array_unshift($this->processors, $processor);

        return $this;
    }

    /**
     * Pops the top-most processor in the stack.
     *
     * @throws \UnderflowException
     *
     * @return callable
     */
    public function popProcessor(): callable
    {
        if (empty($this->processors)) {
            throw new \UnderflowException('You have not added any processors yet!');
        }

        return array_shift($this->processors);
    }

    /**
     * Returns the current stack of processors.
     *
     * @return callable[]
     */
    public function getProcessors(): array
    {
        return $this->processors ?: [];
    }

    /**
     * Passes the message and recipients through all the available processors.
     *
     * @param MessageInterface    $message
     * @param RecipientCollection $recipients
     *
     * @return array
     */
    public function process(MessageInterface $message, RecipientCollection $recipients): array
    {
        if (empty($this->processors)) {
            return [$message, $recipients];
        }
        foreach ($this->processors as $processor) {
            list($message, $recipients) = call_user_func($processor, $message, $recipients);
        }

        return [$message, $recipients];
    }
}
