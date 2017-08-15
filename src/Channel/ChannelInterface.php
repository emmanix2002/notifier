<?php

namespace Emmanix2002\Notifier\Channel;

use Emmanix2002\Notifier\Message\MessageInterface;
use Emmanix2002\Notifier\Recipient\RecipientCollection;

interface ChannelInterface
{
    /**
     * The name of the channel.
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Whether or not this channel allows the notification to be propagated further.
     *
     * @return bool
     */
    public function propagate(): bool;

    /**
     * Notifies the channel to process the message for the provided recipients.
     * The channel will return either TRUE (to continue notifying other channels specified), FALSE (to stop from
     * sending to other channels), or a Non-Boolean value to both stop propagating and return to the caller as
     * a response.
     *
     * @param MessageInterface    $message
     * @param RecipientCollection $recipients
     *
     * @return mixed
     */
    public function notify(MessageInterface $message, RecipientCollection $recipients);
}
