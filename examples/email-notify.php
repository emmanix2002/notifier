<?php

use Emmanix2002\Notifier\Handler\SendgridEmailHandler;
use Emmanix2002\Notifier\Message\SendgridEmailMessage;
use Emmanix2002\Notifier\Notifier;
use Emmanix2002\Notifier\Recipient\RecipientCollection;
use Emmanix2002\Notifier\Recipient\SendgridEmailRecipient;

$baseDir = dirname(__DIR__);
require_once($baseDir.'/vendor/autoload.php');

Notifier::loadEnv();
$notifier = new Notifier('sendgrid', [
    new SendgridEmailHandler(getenv('SENDGRID_KEY'))
]);
dump($notifier);
$message = new SendgridEmailMessage(
        'from@example.com',
        'Welcome from Us',
        null,
        null
);
$message->setFromName('Support')
        ->setHtml('<html><body><strong>Hello</strong> world,<br>this is an email!</body></html>')
        ->setCategory('notifier-test');
$destinations = [];
$addresses = ['id@domain.com', 'id2@domain.com'];
foreach ($addresses as $id => $address) {
    $destinations[] = new SendgridEmailRecipient($address);
}
$recipients = new RecipientCollection($destinations, SendgridEmailRecipient::class);
dump($recipients);
$notifier->notify(
        $message,
        $recipients,
        'sendgrid'
);