<?php

namespace Emmanix2002\Notifier\Handler;

use Emmanix2002\Notifier\Message\EmailMessage;
use Emmanix2002\Notifier\Message\MessageInterface;
use Emmanix2002\Notifier\Message\SendgridEmailMessage;
use Emmanix2002\Notifier\Recipient\RecipientCollection;
use Emmanix2002\Notifier\Recipient\SendgridEmailRecipient;
use SendGrid\Content;
use SendGrid\Email;
use SendGrid\Mail;
use SendGrid\Personalization;

class SendgridEmailHandler extends AbstractHandler
{
    /**
     * @var string
     */
    private $key;

    /**
     * @var bool
     */
    private $stopPropagation;

    /**
     * SendgridEmailHandler constructor.
     *
     * @param string $key
     * @param bool   $stopPropagation
     */
    public function __construct(string $key, bool $stopPropagation = true)
    {
        $this->key = (string) $key;
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
     * It performs an action on the received message; sending it to all the recipients.
     *
     * @param MessageInterface    $message
     * @param RecipientCollection $recipients
     *
     * @throws \InvalidArgumentException
     *
     * @return bool
     */
    public function handle(MessageInterface $message, RecipientCollection $recipients): bool
    {
        try {
            if (!$message instanceof EmailMessage) {
                throw new \InvalidArgumentException('The message need to be an instance of EmailMessage');
            }
            $sendGrid = new \SendGrid($this->key);
            $mail = new Mail();
            $mail->setFrom(new Email($message->getFromName() ?: null, $message->getFrom()));
            $mail->setSubject($message->getSubject());
            $mail->setReplyTo(new Email(null, $message->getReplyTo() ?: $message->getFrom()));
            // set some general properties on the mail
            $contentType = $message->isPlain() ? 'text/plain' : 'text/html';
            $content = new Content($contentType, $message->getBody());
            // create the content
            $personalizationTemplate = new Personalization();
            // we create the template object
            foreach ($recipients as $id => $recipient) {
                // we process substitutions for all the recipients
                $personalization = clone $personalizationTemplate;
                // clone the shit
                $personalization->addTo(new Email(null, $recipient->getAddress()));
                // add the address
                if ($recipient instanceof SendgridEmailRecipient && !empty($recipient->getSubstitutions())) {
                    foreach ($recipient->getSubstitutions() as $key => $value) {
                        $personalization->addSubstitution($key, $value);
                        // add recipient's substitutions
                    }
                }
                if (!empty($recipient->getCc())) {
                    foreach ($recipient->getCc() as $cc) {
                        $personalization->addCc(new Email(null, $cc));
                    }
                }
                if (!empty($recipient->getBcc())) {
                    foreach ($recipient->getBcc() as $bcc) {
                        $personalization->addBcc(new Email(null, $bcc));
                    }
                }
                $mail->addPersonalization($personalization);
                // add the personalisation
            }
            if ($message instanceof SendgridEmailMessage) {
                if ($message->usesTemplate()) {
                    $mail->setTemplateId($message->getBody());
                } else {
                    $mail->addContent($content);
                }
                if (!empty($message->getCategory())) {
                    $mail->addCategory($message->getCategory());
                }
                if (!empty($message->getSections())) {
                    foreach ($message->getSections() as $key => $value) {
                        $mail->addSection($key, $value);
                    }
                }
            }
            // compose the email
            $response = $sendGrid->client->mail()->send()->post($mail);
            if ($this->stopPropagation) {
                // not going any further
                return $response;
            }
        } catch (\Throwable $e) {
            $this->processException($e);
        }

        return $this->propagate();
    }
}
