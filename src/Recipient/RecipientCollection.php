<?php

namespace Emmanix2002\Notifier\Recipient;

class RecipientCollection extends \ArrayIterator
{
    /**
     * An array of addresses to be used in instantiating the individual recipient objects.
     *
     * @var array
     */
    protected $addresses;

    /**
     * @var string
     */
    protected $recipientClass;

    /**
     * RecipientCollection constructor.
     *
     * @param array  $addresses
     * @param string $recipientClass
     *
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    public function __construct(array $addresses, string $recipientClass)
    {
        if (!$this->checkClass($recipientClass)) {
            throw new \InvalidArgumentException('The recipient class must implement RecipientInterface');
        }
        parent::__construct($addresses);
        $this->addresses = $addresses;
        $this->recipientClass = $recipientClass;
    }

    /**
     * Checks that the recipient class is an instance of RecipientInterface.
     *
     * @param string $className
     *
     * @throws \RuntimeException
     *
     * @return bool
     */
    private function checkClass(string $className)
    {
        try {
            $reflection = new \ReflectionClass($className);
            $interfaces = $reflection->getInterfaceNames();
            foreach ($interfaces as $interface) {
                if ($interface === RecipientInterface::class) {
                    return true;
                }
            }
        } catch (\ReflectionException $e) {
            throw new \RuntimeException('Could not reflect on the recipient class provided');
        }

        return false;
    }

    /**
     * Returns the class container for the addresses.
     *
     * @return string
     */
    public function getRecipientClass()
    {
        return $this->recipientClass;
    }

    /**
     * Create a new collection instance.
     *
     * @param array $addresses
     * @param       $recipientClass
     *
     * @throws \InvalidArgumentException
     *
     * @return static
     */
    public static function make(array $addresses, string $recipientClass)
    {
        return new static($addresses, $recipientClass);
    }

    /**
     * Get all of the items in the collection.
     *
     * @return array
     */
    public function all()
    {
        return $this->addresses;
    }

    /**
     * @return RecipientInterface
     */
    public function current()
    {
        $recipient = parent::current();
        if ($recipient instanceof RecipientInterface) {
            return $recipient;
        }
        if (is_array($recipient)) {
            return new $this->recipientClass(...$recipient);
        }

        return new $this->recipientClass($recipient);
    }

    /**
     * @param string $index
     *
     * @return mixed
     */
    public function offsetGet($index)
    {
        $recipient = $this->addresses[$index];
        if ($recipient instanceof RecipientInterface) {
            return $recipient;
        }
        if (is_array($recipient)) {
            return new $this->recipientClass(...$recipient);
        }

        return new $this->recipientClass($recipient);
    }
}
