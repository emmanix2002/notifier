<?php

use Emmanix2002\Notifier\Handler\InfobipSmsHandler;
use Emmanix2002\Notifier\Message\SmsMessage;
use Emmanix2002\Notifier\Notifier;
use Emmanix2002\Notifier\Processor\SmsMessageProcessor;
use Emmanix2002\Notifier\Recipient\PhoneRecipient;
use Emmanix2002\Notifier\Recipient\RecipientCollection;

$baseDir = dirname(__DIR__);
require_once($baseDir.'/vendor/autoload.php');

Notifier::loadEnv();
$notifier = new Notifier('sms', [
    new InfobipSmsHandler(getenv('INFOBIP_USERNAME'), getenv('INFOBIP_PASSWORD'), '2348136680801')
]);
$notifier->getChannel('sms')->pushProcessor(new SmsMessageProcessor());
dump($notifier);
$message = new SmsMessage('Hi, this is the new notifier messaging you ');
$recipients = new RecipientCollection(['2348136680801', '2348027593878'], PhoneRecipient::class);
$notifier->notify($message, $recipients, 'sms');