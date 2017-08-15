<?php

namespace Emmanix2002\Notifier\Recipient;

class EmailRecipient implements RecipientInterface
{
    /**
     * @var string
     */
    protected $email;

    /**
     * @var array
     */
    protected $cc = [];

    /**
     * @var array
     */
    protected $bcc = [];

    /**
     * EmailRecipient constructor.
     *
     * @param string $email
     * @param array  $cc
     * @param array  $bcc
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(string $email, array $cc = [], array $bcc = [])
    {
        if (!self::validateAddress($email)) {
            throw new \InvalidArgumentException('An invalid email address was provided');
        }
        $this->email = $email;
        $this->setCc($cc)
             ->setBcc($bcc);
    }

    /**
     * Adds an address to the CC list.
     *
     * @param string $email
     *
     * @return $this
     */
    public function addCc(string $email)
    {
        if (!self::validateAddress($email)) {
            return $this;
        }
        $this->cc[] = $email;

        return $this;
    }

    /**
     * Adds an email to the BCC list.
     *
     * @param string $email
     *
     * @return $this
     */
    public function addBcc(string $email)
    {
        if (!self::validateAddress($email)) {
            return $this;
        }
        $this->bcc[] = $email;

        return $this;
    }

    /**
     * Sets the CC addresses.
     *
     * @param array $emails
     *
     * @return $this
     */
    public function setCc(array $emails)
    {
        if (empty($emails)) {
            return $this;
        }
        $filtered = array_filter($emails, [self::class, 'validateAddress']);
        $this->cc = $filtered;

        return $this;
    }

    /**
     * Sets the BCC copy addresses.
     *
     * @param array $emails
     *
     * @return $this
     */
    public function setBcc(array $emails)
    {
        if (empty($emails)) {
            return $this;
        }
        $filtered = array_filter($emails, [self::class, 'validateAddress']);
        $this->bcc = $filtered;

        return $this;
    }

    /**
     * Returns the BCC addresses.
     *
     * @return array
     */
    public function getBcc(): array
    {
        return (array) $this->bcc;
    }

    /**
     * Returns the CC addresses.
     *
     * @return array
     */
    public function getCc(): array
    {
        return (array) $this->cc;
    }

    /**
     * This returns the address for this recipient. This address can be anything:
     * email, phone number, array -- all that matters is that it can be used to identify a destination
     * for the handler.
     *
     * @return string
     */
    public function getAddress()
    {
        return (string) $this->email;
    }

    /**
     * Validates an email address.
     *
     * @param string $email
     *
     * @return mixed
     */
    public static function validateAddress(string $email)
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }
}
