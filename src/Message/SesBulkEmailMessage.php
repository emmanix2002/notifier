<?php

namespace Emmanix2002\Notifier\Message;


class SesBulkEmailMessage extends SesEmailMessage
{
    /**
     * @var string|null
     */
    private $textBody = null;

    /**
     * @var null|string
     */
    private $configSet = null;

    /**
     * SesBulkEmailMessage constructor.
     *
     * @param string|null $from
     * @param string|null $subject
     * @param string|null $replyTo
     * @param string|null $htmlBody
     * @param string|null $textBody
     * @param string|null $configSet
     */
    public function __construct(
        string $from = null,
        string $subject = null,
        string $replyTo = null,
        string $htmlBody = null,
        string $textBody = null,
        string $configSet = null
    )
    {
        parent::__construct($from, $subject, $htmlBody, $replyTo);
        $this->textBody = $textBody;
        $this->configSet = $configSet;
    }

    /**
     * @return string
     */
    public function getConfigSet(): string
    {
        return (string) $this->configSet;
    }

    public function getHtmlBody(): string
    {
        return $this->getBody();
    }

    /**
     * @return string
     */
    public function getTextBody(): string
    {
        return $this->textBody ?: $this->toPlainText();
    }

    /**
     * @param string $name
     *
     * @return SesBulkEmailMessage
     */
    public function setConfigSet(string $name): SesBulkEmailMessage
    {
        $this->configSet = $name;
        return $this;
    }

}