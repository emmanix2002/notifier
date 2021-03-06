<?php

namespace Emmanix2002\Notifier;

use Dotenv\Dotenv;
use Dotenv\Exception\InvalidPathException;
use Emmanix2002\Notifier\Channel\Channel;
use Emmanix2002\Notifier\Channel\ChannelInterface;
use Emmanix2002\Notifier\Message\MessageInterface;
use Emmanix2002\Notifier\Recipient\RecipientCollection;
use Monolog\Handler\ErrorLogHandler;
use Monolog\Logger;
use Psr\Log\LoggerInterface;

class Notifier
{
    use ManagesChannels, ManagesProcessors;

    /**
     * An array of channels present in this notifier instance.
     *
     * @var ChannelInterface[]
     */
    protected $channels;

    /**
     * The stack of processors.
     *
     * @var array
     */
    protected $processors;

    /**
     * @var LoggerInterface
     */
    private static $logger = null;

    /**
     * @var Notifier
     */
    private static $instance;

    /**
     * Notifier constructor.
     * It automatically creates a new channel with the provided name, and sets the handlers on it.
     *
     * @param string $name
     * @param array  $handlers
     */
    public function __construct(string $name, array $handlers = [])
    {
        $this->addChannel(Channel::named($name, $handlers));
    }

    /**
     * Provides a static interface to getting the Notifier.
     *
     * @param string $name
     * @param array  $handlers
     *
     * @return static
     */
    public static function instance(string $name, array $handlers = [])
    {
        if (self::$instance === null) {
            self::$instance = new static($name, $handlers);
        }

        return self::$instance;
    }

    /**
     * Gets the current channel list.
     *
     * @return ChannelInterface[]
     */
    public function getChannels()
    {
        return $this->channels;
    }

    /**
     * Sends the notification.
     *
     * @param MessageInterface    $message
     * @param RecipientCollection $recipients
     * @param \string[]           ...$channelNames
     *
     * @throws \InvalidArgumentException
     * @throws \UnderflowException
     *
     * @return array
     */
    final public function notify(MessageInterface $message, RecipientCollection $recipients, string ...$channelNames): array
    {
        if (empty($this->channels)) {
            throw new \UnderflowException('There are no channels configured');
        }
        if (empty($channelNames)) {
            throw new \InvalidArgumentException('You need to specify at least one channel to send the message through');
        }
        list($message, $recipients) = $this->process($message, $recipients);
        // send data for processing through all attached processors
        $responses = [];
        // channel responses
        foreach ($this->channels as $channel) {
            if (!in_array($channel->getName(), $channelNames, true)) {
                continue;
            }
            $responses[$channel->getName()] = $channel->notify($message, $recipients);
            // we collect the response
            if (!is_bool($responses[$channel->getName()]) || !$responses[$channel->getName()]) {
                // this channel does not support bubbling
                break;
            }
        }

        return $responses;
    }

    /**
     * Loads the environment variables.
     * If this library was not installed using composer, it loads settings from the library's root directory, else
     * it tries to load settings from the directory which is the parent to the composer vendor directory.
     */
    public static function loadEnv()
    {
        try {
            $vendorDir = dirname(__DIR__, 3);
            $loadDir = substr($vendorDir, -6) === 'vendor' ? dirname($vendorDir) : dirname(__DIR__);
            // if the /vendor path doesn't exist in the variable, use the current directory, else use the project dir
            $dotEnv = new Dotenv($loadDir);
            $dotEnv->load();
        } catch (InvalidPathException $e) {
            self::getLogger()->error($e->getMessage());
        }
    }

    /**
     * Returns the logger.
     *
     * @return LoggerInterface
     */
    public static function getLogger(): LoggerInterface
    {
        if (self::$logger === null) {
            self::$logger = new Logger(__CLASS__);
            self::$logger->pushHandler(new ErrorLogHandler(ErrorLogHandler::OPERATING_SYSTEM, Logger::WARNING));
        }

        return self::$logger;
    }
}
