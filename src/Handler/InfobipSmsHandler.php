<?php

namespace Emmanix2002\Notifier\Handler;

use Emmanix2002\Notifier\Message\InfobipSmsMessage;
use Emmanix2002\Notifier\Message\MessageInterface;
use Emmanix2002\Notifier\Message\SmsMessage;
use Emmanix2002\Notifier\Recipient\RecipientCollection;
use infobip\api\client\SendMultipleTextualSmsAdvanced;
use infobip\api\configuration\BasicAuthConfiguration;
use infobip\api\model\Destination;
use infobip\api\model\sms\mt\send\Message;
use infobip\api\model\sms\mt\send\textual\SMSAdvancedTextualRequest;
use Ramsey\Uuid\Uuid;

/**
 * This handler uses the Infobip infrastructure to send SMS messages to the destination phone numbers.
 * Numbers are expected to be in the international format i.e. with the country code present
 * For a Nigerian number: 08123456789, the destination would be: 2348123456789
 *
 * @package Emmanix2002\Notifier\Handler
 * @link https://dev.infobip.com/docs/fully-featured-textual-message
 */
class InfobipSmsHandler implements HandlerInterface
{
    /**
     * @var string
     */
    private $username;
    
    /**
     * @var string
     */
    private $password;
    
    /**
     * @var string
     */
    private $from;
    
    /**
     * InfobipSmsHandler constructor.
     *
     * @param string $username
     * @param string $password
     * @param string $fromPhone
     */
    public function __construct(string $username, string $password, string $fromPhone)
    {
        $this->username = $username;
        $this->password = $password;
        $this->from = $fromPhone;
    }
    
    /**
     * The phone number it is from
     *
     * @param string $phoneNumber
     *
     * @return $this
     */
    public function setFrom(string $phoneNumber)
    {
        $this->from = $phoneNumber;
        return $this;
    }
    
    /**
     * Returns the authentication configuration object
     *
     * @return BasicAuthConfiguration
     */
    protected function getAuthConfig(): BasicAuthConfiguration
    {
        return new BasicAuthConfiguration($this->username, $this->password);
    }
    
    /**
     * Returns the SMS client to be used
     *
     * @return SendMultipleTextualSmsAdvanced
     */
    public function getClient()
    {
        return new SendMultipleTextualSmsAdvanced($this->getAuthConfig());
    }
    
    /**
     * It performs an action on the received message; sending it to all the recipients
     *
     * @param MessageInterface    $message
     * @param RecipientCollection $recipients
     *
     * @return bool
     * @throws \InvalidArgumentException
     */
    public function handle(MessageInterface $message, RecipientCollection $recipients): bool
    {
        try {
            if (! ($message instanceof SmsMessage or $message instanceof InfobipSmsMessage)) {
                throw new \InvalidArgumentException('The message need to be an instance of SmsMessage or InfobipSmsMessage');
            }
            $destinations = [];
            $destination = new Destination();
            foreach ($recipients as $recipient) {
                $d = clone $destination;
                $d->setTo($recipient->getAddress());
                $destinations[] = $d;
            }
            $smsMessage = new Message();
            $smsMessage->setFrom($this->from);
            $smsMessage->setDestinations($destinations);
            $smsMessage->setText($message->getMessage());
            if ($message instanceof InfobipSmsMessage) {
                if ($message->getScheduleFor()) {
                    $smsMessage->setSendAt($message->getScheduleFor());
                }
                if (!empty($message->getNotifyUrl())) {
                    $smsMessage->setNotifyUrl($message->getNotifyUrl());
                    $smsMessage->setNotifyContentType($message->getNotifyContentType());
                }
            }
            $request = new SMSAdvancedTextualRequest();
            $request->setMessages([$smsMessage]);
            if (count($recipients) > 1) {
                $request->setBulkId(Uuid::uuid1()->toString());
            }
            $response = $this->getClient()->execute($request);
            dump($response);
        } catch (\InvalidArgumentException $e) {
            # since it failed, we pass the notification to the next handler irrespective of the choice by this handler
            dump($e);
            return true;
        }
        return $this->propagate();
    }
    
    /**
     * Whether or not to continue propagation
     *
     * @return bool
     */
    public function propagate(): bool
    {
        return false;
    }
}