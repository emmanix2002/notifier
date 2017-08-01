<?php

namespace Emmanix2002\Notifier\Handler;


use Aws\Ses\SesClient;
use Emmanix2002\Notifier\Message\EmailMessage;
use Emmanix2002\Notifier\Message\MessageInterface;
use Emmanix2002\Notifier\Message\SesEmailMessage;
use Emmanix2002\Notifier\Notifier;
use Emmanix2002\Notifier\Recipient\EmailRecipient;
use Emmanix2002\Notifier\Recipient\RecipientCollection;

class AmazonSesEmailHandler extends AbstractHandler
{
    /**
     * @var SesClient|null
     */
    private $sesClient;

    /**
     * @var bool
     */
    private $stopPropagation;

    /**
     * AmazonSesEmailHandler constructor.
     *
     * @param SesClient|null $client
     * @param bool           $stopPropagation
     */
    public function __construct(SesClient $client = null, bool $stopPropagation = true)
    {
        $this->sesClient = $client;
        $this->stopPropagation = $stopPropagation;
    }

    /**
     * @inheritdoc
     */
    public function propagate(): bool
    {
        return !$this->stopPropagation;
    }

    /**
     * Sets the SesClient to use with the handler
     *
     * @param SesClient $client
     *
     * @return $this
     */
    public function setClient(SesClient $client)
    {
        $this->sesClient = $client;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function handle(MessageInterface $message, RecipientCollection $recipients)
    {
        $chunkResults = [];
        # our email results
        try {
            if (!$message instanceof EmailMessage) {
                throw new \InvalidArgumentException('The message need to be an instance of EmailMessage');
            }
            if (empty($message->getSubject())) {
                throw new \UnexpectedValueException('You have not added an email subject');
            }
            if (empty($recipients) || count($recipients) === 0) {
                throw new \UnderflowException('You need to provide at least one recipient for the email');
            }
            if (empty($message->getBody())) {
                throw new \UnexpectedValueException('You have not added the message to be sent.');
            }
            $message = ! $message instanceof SesEmailMessage ? SesEmailMessage::instance($message) : $message;
            # convert the message to an SesEmailMessage object
            $recipientsAsArray = $recipients->getArrayCopy();
            # we get a copy of the array
            $chunks = array_chunk($recipientsAsArray, 50);
            # we break up everything into chunks of 50
            foreach ($chunks as $chunk) {
                # process them one chunk at a time
                $messageBody = [
                    'Text' => ['Data' => $message->toPlainText(), 'Charset' => 'utf-8']
                ];
                if (!$message->isPlain()) {
                    # not a plain text only message
                    $messageBody['Html'] = ['Data' => $message->getBody(), 'Charset' => 'utf-8'];
                }
                $addresses = ['To' => [], 'Cc' => [], 'Bcc' => []];
                # our address container
                $destinations = new RecipientCollection($chunk, $recipients->getRecipientClass());
                # we create the destinations
                foreach ($destinations as $recipient) {
                    if (! $recipient instanceof EmailRecipient) {
                        continue;
                    }
                    $addresses['To'][] = $recipient->getAddress();
                    if (!empty($recipient->getCc())) {
                        $addresses['Cc'] = array_merge($addresses['Cc'], $recipient->getCc());
                    }
                    if (!empty($recipient->getBcc())) {
                        $addresses['Bcc'] = array_merge($addresses['Cc'], $recipient->getBcc());
                    }
                }
                $chunkResults[] = $this->sesClient->sendEmail([
                    'Source' => $message->getFrom(),
                    'Destination' => [
                        'ToAddresses' => $addresses['To'],
                        'CcAddresses' => $addresses['Cc'],
                        'BccAddresses' => $addresses['Bcc'],
                    ],
                    'Message' => [
                        'Subject' => ['Data' => $message->getSubject()],
                        'Body' => $messageBody
                    ]
                ]);
            }
            if ($this->stopPropagation) {
                # not going any further
                return count($chunkResults) === 1 ? $chunkResults[0] : $chunkResults;
            }

        } catch (\InvalidArgumentException $e) {
            # since it failed, we pass the notification to the next handler irrespective of the choice by this handler
            Notifier::getLogger()->error($e->getMessage());
        } catch (\Exception $e) {
            Notifier::getLogger()->error($e->getMessage());
        }
        return $this->propagate();
    }
}