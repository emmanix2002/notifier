<?php

namespace Emmanix2002\Notifier\Message;

class InfobipSmsMessage extends SmsMessage
{
    
    const NOTIFY_CONTENT_TYPE_JSON = 'application/json';
    
    const NOTIFY_CONTENT_TYPE_XML = 'application/xml';
    
    /**
     * What time to schedule the message for
     *
     * @var \DateTime|null
     */
    protected $scheduledFor;
    
    /**
     * @var string
     */
    protected $notifyUrl;
    
    /**
     * @var string
     */
    protected $notifyContentType;
    
    /**
     * InfobipSmsMessage constructor.
     *
     * @param null           $message
     * @param \DateTime|null $scheduleFor
     */
    public function __construct($message = null, \DateTime $scheduleFor = null)
    {
        parent::__construct($message);
        $this->scheduledFor = $scheduleFor;
    }
    
    /**
     * Schedule the message for a set time
     *
     * @param \DateTime $dateTime
     *
     * @return $this
     */
    public function setScheduleFor(\DateTime $dateTime)
    {
        if (new \DateTime() <= $dateTime) {
            $this->scheduledFor = $dateTime;
        }
        return $this;
    }
    
    /**
     * @return \DateTime|null
     */
    public function getScheduleFor()
    {
        return $this->scheduledFor;
    }
    
    /**
     * Set the notification URL where delivery reports should be delivered to
     *
     * @param string $notifyUrl
     * @param string $contentType   the format of data to be sent to the URL
     *
     * @return $this
     */
    public function setNotifyUrl(string $notifyUrl, string $contentType = self::NOTIFY_CONTENT_TYPE_JSON)
    {
        $this->notifyUrl = $notifyUrl;
        $types = [self::NOTIFY_CONTENT_TYPE_JSON, self::NOTIFY_CONTENT_TYPE_XML];
        $contentType = !in_array($contentType, $types, true) ? self::NOTIFY_CONTENT_TYPE_JSON : $contentType;
        $this->notifyContentType = $contentType;
        return $this;
    }
    
    /**
     * @return string
     */
    public function getNotifyUrl(): string
    {
        return (string) $this->notifyUrl;
    }
    
    /**
     * @return string
     */
    public function getNotifyContentType(): string
    {
        return $this->notifyContentType ?: self::NOTIFY_CONTENT_TYPE_JSON;
    }
}