<?php

namespace Emmanix2002\Notifier\Message;

use Emmanix2002\Notifier\Recipient\EmailRecipient;

class EmailMessage implements MessageInterface
{
    /**
     * @var string
     */
    protected $subject;

    /**
     * @var string
     */
    protected $body;

    /**
     * @var string
     */
    protected $from;

    /**
     * @var string
     */
    protected $fromName;

    /**
     * @var string|null
     */
    protected $replyTo;

    /**
     * @var bool
     */
    protected $isPlain;

    /**
     * EmailMessage constructor.
     *
     * @param string|null $from    the sender email address
     * @param string|null $subject the email subject
     * @param string|null $body    the plain text email message
     * @param string|null $replyTo the replyTo email address (default: $from)
     */
    public function __construct(string $from = null, string $subject = null, string $body = null, string $replyTo = null)
    {
        $from = !empty($from) ? EmailRecipient::validateAddress($from) : null;
        $replyTo = !empty($replyTo) ? EmailRecipient::validateAddress($replyTo) : null;
        $this->subject = (string) $subject;
        $this->body = (string) $body;
        $this->from = !empty($from) ? (string) $from : null;
        $this->replyTo = !empty($replyTo) ? (string) $replyTo : $this->from;
        $this->isPlain = true;
    }

    /**
     * Sets the FROM address.
     *
     * @param string $from
     *
     * @throws \InvalidArgumentException
     *
     * @return $this
     */
    public function setFrom(string $from)
    {
        if (!EmailRecipient::validateAddress($from)) {
            throw new \InvalidArgumentException('An invalid from address was provided');
        }
        $this->from = $from;

        return $this;
    }

    /**
     * Sets the from name.
     *
     * @param string $fromName
     *
     * @return $this
     */
    public function setFromName(string $fromName)
    {
        $this->fromName = (string) $fromName;

        return $this;
    }

    /**
     * Sets the Reply-To address.
     *
     * @param string $replyTo
     *
     * @throws \InvalidArgumentException
     *
     * @return $this
     */
    public function setReplyTo(string $replyTo)
    {
        if (!EmailRecipient::validateAddress($replyTo)) {
            throw new \InvalidArgumentException('An invalid replyTo address was provided');
        }
        $this->replyTo = $replyTo;

        return $this;
    }

    /**
     * Sets the subject.
     *
     * @param string $subject
     *
     * @return $this
     */
    public function setSubject(string $subject)
    {
        $this->subject = $subject;

        return $this;
    }

    /**
     * Sets the message as a HTML message.
     *
     * @param string $html
     *
     * @return $this
     */
    public function setHtml(string $html)
    {
        $this->isPlain = false;
        $this->body = $html;

        return $this;
    }

    /**
     * Sets the message as a plain text message.
     *
     * @param string $text
     *
     * @return $this
     */
    public function setPlaintext(string $text)
    {
        $this->isPlain = true;
        $this->body = $text;

        return $this;
    }

    /**
     * @param bool $withName
     *
     * @return string
     */
    public function getFrom(bool $withName = true): string
    {
        if (!$withName || empty($this->getFromName())) {
            return (string) $this->from;
        }
        if ($withName && !empty($this->getFromName())) {
            return $this->getFromName()." <".$this->from.">";
        }
        return $this->from;
    }

    /**
     * @return string
     */
    public function getFromName(): string
    {
        return (string) $this->fromName;
    }

    /**
     * @return string
     */
    public function getReplyTo(): string
    {
        return (string) $this->replyTo;
    }

    /**
     * @return string
     */
    public function getSubject(): string
    {
        return (string) $this->subject;
    }

    /**
     * @return string
     */
    public function getBody(): string
    {
        return (string) $this->body;
    }

    /**
     * @return bool
     */
    public function isPlain(): bool
    {
        return $this->isPlain;
    }

    /**
     * Returns the content of the body of the message.
     *
     * @return string
     */
    public function getMessage()
    {
        return $this;
    }

    /**
     * Allows the message to be set.
     *
     * @param $message
     *
     * @throws \InvalidArgumentException
     *
     * @return $this
     */
    public function setMessage($message)
    {
        if (!$message instanceof self) {
            throw new \InvalidArgumentException('setMessage expects an instance of EmailMessage');
        }
        $this->from = $message->getFrom();
        $this->fromName = $message->getFromName();
        $this->subject = $message->getSubject();
        $this->body = $message->getBody();
        $this->replyTo = $message->getReplyTo();
        $this->isPlain = $message->isPlain();

        return $this;
    }
}
