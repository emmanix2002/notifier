<?php

namespace Emmanix2002\Notifier\Handler;

use Emmanix2002\Notifier\Message\EmailMessage;
use Emmanix2002\Notifier\Message\MessageInterface;
use Emmanix2002\Notifier\Message\SendgridEmailMessage;
use Emmanix2002\Notifier\Notifier;
use Emmanix2002\Notifier\Recipient\RecipientCollection;
use Emmanix2002\Notifier\Recipient\SendgridEmailRecipient;
use SendGrid\Email;
use SendGrid\Personalization;

class SendgridEmailHandler implements HandlerInterface
{
    /**
     * @var string
     */
    private $key;
    
    /**
     * SendgridEmailHandler constructor.
     *
     * @param string $key
     */
    public function __construct(string $key)
    {
        $this->key = (string) $key;
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
            if (!($message instanceof SendgridEmailMessage || $message instanceof EmailMessage)) {
                throw new \InvalidArgumentException('The message need to be an instance of EmailMessage or SendgridEmailMessage');
            }
            $sendGrid = new \SendGrid($this->key);
            $validRecipients = [];
            $substitutionKeys = [];
            foreach ($recipients as $recipient) {
                if (!$recipient->getAddress()) {
                    continue;
                }
                $validRecipients[] = $recipient;
                if (!$recipient instanceof SendgridEmailRecipient) {
                    continue;
                }
                if (empty($recipient->getSubstitutions())) {
                    continue;
                }
                $keys = array_keys($recipient->getSubstitutions());
                $substitutionKeys = array_merge($substitutionKeys, $keys);
            }
            $substitutionKeys = array_unique($substitutionKeys);
            $substitutions = $this->processSubstitutions($validRecipients, $substitutionKeys);
            # get the substitutions, if any
            $mail = new \SendGrid\Mail();
            $mail->setFrom(new Email($message->getFromName() ?: null, $message->getFrom()));
            $mail->setSubject($message->getSubject());
            $mail->setReplyTo(new Email(null, $message->getReplyTo() ?: $message->getFrom()));
            # set some general properties on the mail
            $personalizationTemplate = new Personalization();
            # we create the template object
            foreach ($validRecipients as $id => $recipient) {
                # we process substitutions for all the recipients
                $personalization = clone $personalizationTemplate;
                # clone the shit
                $personalization->addTo(new Email(null, $recipient->getAddress()));
                # add the address
                foreach ($substitutions as $key => $subSet) {
                    $personalization->addSubstitution($key, $subSet[$id]);
                    # add his/her substitutions
                }
                $mail->addPersonalization($personalization);
                # add the personalisation
            }
            if ($message instanceof SendgridEmailMessage) {
                if ($message->usesTemplate()) {
                    $mail->setTemplateId($message->getBody());
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
            # compose the email
            $response = $sendGrid->client->mail()->send()->post($mail);
            Notifier::getLogger()->debug('Response', ['data' => $response]);
        } catch (\InvalidArgumentException $e) {
            # since it failed, we pass the notification to the next handler irrespective of the choice by this handler
            Notifier::getLogger()->error($e->getMessage());
            return true;
        }
        return $this->propagate();
    }
    
    /**
     * Creates the substitutions array from all the recipients passed
     *
     * @param array $recipients
     * @param array $substitutionKeys
     *
     * @return array
     */
    private function processSubstitutions(array $recipients, array $substitutionKeys): array
    {
        $substitutions = [];
        if (!empty($substitutionKeys)) {
            foreach ($substitutionKeys as $subKey) {
                $substitutions[$subKey] = [];
            }
        }
        foreach ($recipients as $recipient) {
            if (!$recipient instanceof SendgridEmailRecipient) {
                $substitutions = $this->fillSlots($substitutionKeys, $substitutions);
                continue;
            }
            if (empty($recipient->getSubstitutions())) {
                $substitutions = $this->fillSlots($substitutionKeys, $substitutions);
                continue;
            }
            if (empty($substitutionKeys)) {
                continue;
            }
            $userSubs = $recipient->getSubstitutions();
            foreach ($substitutionKeys as $subKey) {
                $substitutions[$subKey][] = array_key_exists($subKey, $userSubs) ? $userSubs[$subKey] : '';
            }
        }
        return $substitutions;
    }
    
    /**
     * Fills the empty slots in the substitutions
     *
     * @param array $keys
     * @param array $substitutions
     *
     * @return array
     */
    private function fillSlots(array $keys, array $substitutions): array
    {
        if (empty($keys)) {
            return [];
        }
        foreach ($keys as $key) {
            $substitutions[$key][] = '';
        }
        return $substitutions;
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