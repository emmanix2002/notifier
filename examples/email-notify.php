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
        'Welcome from Grace',
        null,
        null,
        [':actionUrl' => 'https://make-stuff-happen.io']
);
$message->setFromName('Support')
        ->setTemplateId('1bc1e985-e975-4051-88e8-6bca813e75fd')
        ->setCategory('notifier-test');
$names = ['Emmanuel', 'Jason'];
$urls = [':actionUrl', ':actionUrl'];
$recipients = [];
$addresses = ['id@domain.com', 'id2@domain.com'];
foreach ($addresses as $id => $address) {
    $recipients[] = new SendgridEmailRecipient($address, ['-name-' => $names[$id], '-actionUrl-' => $urls[$id]]);
}
dump($recipients);
$notifier->notify(
        $message,
        new RecipientCollection($recipients, SendgridEmailRecipient::class),
        'sendgrid'
);