<?php

namespace Emmanix2002\Notifier\Handler;

use Aws\Ses\SesClient;
use Emmanix2002\Notifier\Message\EmailMessage;
use Emmanix2002\Notifier\Message\MessageInterface;
use Emmanix2002\Notifier\Message\SesBulkEmailMessage;
use Emmanix2002\Notifier\Message\SesEmailMessage;
use Emmanix2002\Notifier\Recipient\EmailRecipient;
use Emmanix2002\Notifier\Recipient\RecipientCollection;
use Emmanix2002\Notifier\Recipient\SesBulkEmailRecipient;
use Ramsey\Uuid\Uuid;

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
     * {@inheritdoc}
     */
    public function propagate(): bool
    {
        return !$this->stopPropagation;
    }

    /**
     * Sets the SesClient to use with the handler.
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
     * Creates a template for the bulk message delivery
     *
     * @param SesBulkEmailMessage $message
     *
     * @return string
     */
    public function createTemplate(SesBulkEmailMessage $message)
    {
        $templateId = (new \DateTime())->format('Y-m-d').'_'.Uuid::uuid1()->toString();
        $response = $this->sesClient->createTemplate([
            'Template' => [
                'HtmlPart' => $message->getHtmlBody(),
                'SubjectPart' => $message->getSubject(),
                'TemplateName' => $templateId,
                'TextPart' => $message->getTextBody(),
            ]
        ]);
        return $templateId;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(MessageInterface $message, RecipientCollection $recipients)
    {
        $chunkResults = [];
        // our email results
        try {
            if (!$message instanceof EmailMessage) {
                throw new \InvalidArgumentException('The message need to be an instance of EmailMessage');
            }
            if (!is_a($recipients->getRecipientClass(), EmailRecipient::class, true)) {
                throw new \InvalidArgumentException('The recipient needs to be an instance [or subclass] of EmailRecipient');
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
            $message = !$message instanceof SesEmailMessage ? SesEmailMessage::instance($message) : $message;
            // convert the message to an SesEmailMessage object
            $recipientsAsArray = $recipients->getArrayCopy();
            // we get a copy of the array
            $chunks = array_chunk($recipientsAsArray, 50);
            // we break up everything into chunks of 50
            foreach ($chunks as $chunk) {
                // process them one chunk at a time
                $recipientsChunk = new RecipientCollection($chunk, $recipients->getRecipientClass());
                // we create the destinations
                if ($message instanceof SesBulkEmailMessage) {
                    $templateId = $this->createTemplate($message);
                    # get the template id
                    $destinations = [];
                    # email destinations
                    $defaultReplacements = [];
                    # the default replacements
                    foreach ($recipientsChunk as $id => $recipient) {
                        # process the recipients
                        if (!$recipient instanceof SesBulkEmailRecipient) {
                            continue;
                        }
                        if ($id === 0) {
                            $defaultReplacements = $recipient->getSubstitutions();
                        }
                        $mergeTags = [];
                        # the merge tags
                        foreach ($recipient->getSubstitutions() as $key => $value) {
                            $mergeTags[] = ['Name' => $key, 'Value' => $value];
                        }
                        $destination['Destination'] = ['ToAddresses' => [$recipient->getAddress()]];
                        if (!empty($recipient->getBcc())) {
                            $destination['Destination']['BccAddresses'] = $recipient->getBcc();
                        }
                        if (!empty($recipient->getCc())) {
                            $destination['Destination']['CcAddresses'] = $recipient->getCc();
                        }
                        $destination['ReplacementTemplateData'] = json_encode($recipient->getSubstitutions());
                        $destinations[] = $destination;
                    }
                    foreach ($defaultReplacements as $key => $value) {
                        $defaultReplacements[$key] = '';
                    }
                    $chunkResults[] = $this->sesClient->sendBulkTemplatedEmail([
                        'Source' => $message->getFrom(),
                        'ConfigurationSetName' => $message->getConfigSet(),
                        'DefaultTemplateData' => json_encode($defaultReplacements),
                        'Template' => $templateId,
                        'Destinations' => $destinations,
                        'ReplyToAddresses' => [$message->getReplyTo()]
                    ]);

                } else {
                    # a different path of action
                    $messageBody = [
                        'Text' => ['Data' => $message->toPlainText(), 'Charset' => 'utf-8'],
                    ];
                    if (!$message->isPlain()) {
                        // not a plain text only message
                        $messageBody['Html'] = ['Data' => $message->getBody(), 'Charset' => 'utf-8'];
                    }
                    $addresses = ['To' => [], 'Cc' => [], 'Bcc' => []];
                    // our address container
                    foreach ($recipientsChunk as $recipient) {
                        if (!$recipient instanceof EmailRecipient) {
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
                        'Source'      => $message->getFrom(),
                        'Destination' => [
                            'ToAddresses'  => $addresses['To'],
                            'CcAddresses'  => $addresses['Cc'],
                            'BccAddresses' => $addresses['Bcc'],
                        ],
                        'Message' => [
                            'Subject' => ['Data' => $message->getSubject()],
                            'Body'    => $messageBody,
                        ],
                    ]);
                }
            }
            if ($this->stopPropagation) {
                // not going any further
                return count($chunkResults) === 1 ? $chunkResults[0] : $chunkResults;
            }
        } catch (\Throwable $e) {
            $this->processException($e);
        }
        return $this->propagate();
    }
}
