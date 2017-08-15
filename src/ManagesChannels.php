<?php

namespace Emmanix2002\Notifier;

use Emmanix2002\Notifier\Channel\ChannelInterface;

trait ManagesChannels
{
    /**
     * Add a new notifier channel.
     *
     * @param ChannelInterface $channel
     *
     * @return $this
     */
    public function addChannel(ChannelInterface $channel)
    {
        if (empty($this->channels)) {
            $this->channels = [];
        }
        $this->channels[] = $channel;

        return $this;
    }

    /**
     * Replaces the channels list.
     *
     * @param ChannelInterface[] ...$channels
     *
     * @return $this
     */
    public function setChannels(ChannelInterface ...$channels)
    {
        $this->channels = [];
        foreach ($channels as $channel) {
            $this->channels[] = $channel;
        }

        return $this;
    }

    /**
     * Removes a channel with the provided name from the channels list.
     *
     * @param string $name  the name of the channel to remove
     * @param int    $count the maximum number of channels to remove
     *
     * @throws \InvalidArgumentException
     *
     * @return $this
     */
    public function removeChannel(string $name, int $count = 1)
    {
        if ($count <= 0) {
            throw new \InvalidArgumentException('You do not need to call this method if you have nothing to remove :(');
        }
        $removed = 0;
        foreach ($this->channels as $id => $channel) {
            if ($removed === $count) {
                break;
            }
            if ($channel->getName() === $name) {
                unset($this->channels[$id]);
                ++$removed;
            }
        }

        return $this;
    }

    /**
     * Finds a channel with the given name.
     *
     * @param string $name
     *
     * @return null|ChannelInterface
     */
    public function getChannel(string $name)
    {
        foreach ($this->channels as $channel) {
            if ($channel->getName() === $name) {
                return $channel;
            }
        }
    }
}
