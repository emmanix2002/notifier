<?php

namespace Emmanix2002\Notifier\Recipient;

use Traversable;

class RecipientCollection implements \ArrayAccess, \IteratorAggregate, \Countable
{
    /**
     * An array of addresses to be used in instantiating the individual recipient objects
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
     */
    public function __construct(array $addresses, string $recipientClass)
    {
        if (!$this->checkClass($recipientClass)) {
            throw new \InvalidArgumentException('The recipient class must implement RecipientInterface');
        }
        $this->addresses = $addresses;
        $this->recipientClass = $recipientClass;
    }
    
    /**
     * Checks that the recipient class is an instance of RecipientInterface
     *
     * @param string $className
     *
     * @return bool
     * @throws \RuntimeException
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
     * Create a new collection instance
     *
     * @param array $addresses
     * @param       $recipientClass
     *
     * @return static
     */
    public static function make(array $addresses = [], string $recipientClass)
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
     * Retrieve an external iterator
     *
     * @link  http://php.net/manual/en/iteratoraggregate.getiterator.php
     * @return Traversable An instance of an object implementing <b>Iterator</b> or
     * <b>Traversable</b>
     * @since 5.0.0
     */
    public function getIterator()
    {
        $recipients = [];
        foreach ($this->addresses as $address) {
            $recipients[] = $address instanceof RecipientInterface ? $address : new $this->recipientClass($address);
        }
        return new \ArrayIterator($recipients);
    }
    
    /**
     * Whether a offset exists
     *
     * @link  http://php.net/manual/en/arrayaccess.offsetexists.php
     *
     * @param mixed $offset <p>
     *                      An offset to check for.
     *                      </p>
     *
     * @return boolean true on success or false on failure.
     * </p>
     * <p>
     * The return value will be casted to boolean if non-boolean was returned.
     * @since 5.0.0
     */
    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->addresses);
    }
    
    /**
     * Offset to retrieve
     *
     * @link  http://php.net/manual/en/arrayaccess.offsetget.php
     *
     * @param mixed $offset <p>
     *                      The offset to retrieve.
     *                      </p>
     *
     * @return mixed Can return all value types.
     * @since 5.0.0
     */
    public function offsetGet($offset)
    {
        $address = $this->addresses[$offset];
        return $address instanceof RecipientInterface ? $address : new $this->recipientClass($this->addresses[$offset]);
    }
    
    /**
     * Offset to set
     *
     * @link  http://php.net/manual/en/arrayaccess.offsetset.php
     *
     * @param mixed $offset  <p>
     *                      The offset to assign the value to.
     *                      </p>
     * @param mixed $address <p>
     *                      The value to set.
     *                      </p>
     *
     * @return void
     * @since 5.0.0
     */
    public function offsetSet($offset, $address)
    {
        if (empty($offset)) {
            $this->addresses[] = $address;
        } else {
            $this->addresses[$offset] = $address;
        }
    }
    
    /**
     * Offset to unset
     *
     * @link  http://php.net/manual/en/arrayaccess.offsetunset.php
     *
     * @param mixed $offset <p>
     *                      The offset to unset.
     *                      </p>
     *
     * @return void
     * @since 5.0.0
     */
    public function offsetUnset($offset)
    {
        if ($this->offsetExists($offset)) {
            unset($this->addresses[$offset]);
        }
    }
    
    /**
     * Count elements of an object
     *
     * @link  http://php.net/manual/en/countable.count.php
     * @return int The custom count as an integer.
     * </p>
     * <p>
     * The return value is cast to an integer.
     * @since 5.1.0
     */
    public function count()
    {
        return count($this->addresses);
    }
}