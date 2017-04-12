<?php

namespace Emmanix2002\Notifier\Channel;

class Channel extends AbstractChannel
{
    /**
     * Returns a new named channel with the specified handlers set, providing static access
     *
     * @param string     $name
     * @param array|null $handlers
     *
     * @return static
     */
    public static function named(string $name, array $handlers = null)
    {
        return new static($name, $handlers);
    }
    
    /**
     * Whether or not this channel allows the notification to be propagated further
     *
     * @return bool
     */
    public function propagate(): bool
    {
        return true;
    }
}