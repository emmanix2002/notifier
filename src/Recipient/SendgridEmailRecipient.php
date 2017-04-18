<?php

namespace Emmanix2002\Notifier\Recipient;

class SendgridEmailRecipient extends EmailRecipient
{
    /**
     * @var array
     */
    protected $substitutions;
    
    /**
     * SendgridEmailRecipient constructor.
     *
     * @param string $email
     * @param array  $substitutions
     * @param array  $cc
     * @param array  $bcc
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(string $email, array $substitutions = [], array $cc = [], array $bcc = [])
    {
        parent::__construct($email, $cc, $bcc);
        $this->substitutions = (array) $substitutions;
    }
    
    /**
     * Sets the substitutions for this recipient
     *
     * @param array $substitutions
     *
     * @return $this
     */
    public function setSubstitutions(array $substitutions)
    {
        $this->substitutions = (array) $substitutions;
        return $this;
    }
    
    /**
     * Adds a new substitution key
     *
     * @param string $key
     * @param        $value
     * @param bool   $overwrite
     *
     * @return $this
     */
    public function addSubstitution(string $key, $value, bool $overwrite = false)
    {
        if (!$overwrite && array_key_exists($key, $this->substitutions)) {
            return $this;
        }
        $this->substitutions[$key] = $value;
        return $this;
    }
    
    /**
     * @return array
     */
    public function getSubstitutions(): array
    {
        return $this->substitutions;
    }
}