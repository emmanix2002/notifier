<?php

namespace Emmanix2002\Notifier\Channel;

use Emmanix2002\Notifier\Handler\HandlerInterface;
use Emmanix2002\Notifier\ManagesProcessors;
use Emmanix2002\Notifier\Message\MessageInterface;
use Emmanix2002\Notifier\Recipient\RecipientCollection;

abstract class AbstractChannel implements ChannelInterface
{
    use ManagesProcessors;

    /**
     * The channel name.
     *
     * @var string
     */
    protected $name;

    /**
     * The stack of handlers.
     *
     * @var HandlerInterface[]
     */
    protected $handlers;

    /**
     * @var callable[]
     */
    protected $processors;

    /**
     * AbstractChannel constructor.
     *
     * @param string     $name
     * @param array|null $handlers
     */
    public function __construct(string $name, array $handlers = null)
    {
        $this->name = (string) $name;
        if (!empty($handlers)) {
            $this->setHandlers(...$handlers);
        }
    }

    /**
     * Get the current handler stack.
     *
     * @return HandlerInterface[]
     */
    public function getHandlers(): array
    {
        return $this->handlers ?: [];
    }

    /**
     * Adds a handler to the stack.
     *
     * @param HandlerInterface $handler
     *
     * @return $this
     */
    public function pushHandler(HandlerInterface $handler)
    {
        if (empty($this->handler)) {
            $this->handlers = [];
        }
        array_unshift($this->handlers, $handler);

        return $this;
    }

    /**
     * Pops the top-most handler in the stack.
     *
     * @throws \UnderflowException
     *
     * @return HandlerInterface|mixed
     */
    public function popHandler(): HandlerInterface
    {
        if (empty($this->handlers)) {
            throw new \UnderflowException('You have not added any handlers to the channel yet!');
        }

        return array_shift($this->handlers);
    }

    /**
     * Replaces the current set of handlers with a new set.
     *
     * @param HandlerInterface[] ...$handlers
     *
     * @return $this
     */
    public function setHandlers(HandlerInterface ...$handlers)
    {
        $this->handlers = [];
        $handlers = array_reverse($handlers);
        foreach ($handlers as $handler) {
            $this->pushHandler($handler);
        }

        return $this;
    }

    /**
     * The name of the channel.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function notify(MessageInterface $message, RecipientCollection $recipients)
    {
        if (empty($this->handlers)) {
            // no handler here -- so we just bubble to the next channel
            return true;
        }
        $response = null;
        list($message, $recipients) = $this->process($message, $recipients);
        // send for processing to all attached processors
        foreach ($this->handlers as $handler) {
            $response = $handler->handle($message, $recipients);
            // send for processing
            if (!is_bool($response) || !$response) {
                // stop propagation
                break;
            }
        }
        return !is_bool($response) || !$response ? $response : $this->propagate();
    }
}
