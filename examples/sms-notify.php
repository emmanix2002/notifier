<?php

use Emmanix2002\Notifier\Handler\InfobipSmsHandler;
use Emmanix2002\Notifier\Message\MessageInterface;
use Emmanix2002\Notifier\Message\SmsMessage;
use Emmanix2002\Notifier\Notifier;
use Emmanix2002\Notifier\Processor\SmsMessageProcessor;
use Emmanix2002\Notifier\Recipient\PhoneRecipient;
use Emmanix2002\Notifier\Recipient\RecipientCollection;

$baseDir = dirname(__DIR__);
require_once($baseDir.'/vendor/autoload.php');

Notifier::loadEnv();
$notifier = new Notifier('sms', [
    new InfobipSmsHandler(getenv('INFOBIP_USERNAME'), getenv('INFOBIP_PASSWORD'), 'Fansng')
]);
#$notifier->getChannel('sms')->pushProcessor(new SmsMessageProcessor());
$notifier->getChannel('sms')->pushProcessor(function (MessageInterface $message, RecipientCollection $recipients) {
    if (strlen($message->getMessage()) > 140) {
        return [$message->setMessage(substr($message->getMessage(), 0, 140)), $recipients];
    }
    $remainingLength = 140 - strlen($message->getMessage());
    $message->setMessage($message->getMessage().str_repeat('*', $remainingLength));
    return [$message, $recipients];
});
dump($notifier);
$message = new SmsMessage('Hi, the processor will add asterisks to fill the message');
$recipients = new RecipientCollection(['2348136680801', '2348027593878', '2348067319894'], PhoneRecipient::class);
dump($recipients);
$notifier->notify($message, $recipients, 'sms');