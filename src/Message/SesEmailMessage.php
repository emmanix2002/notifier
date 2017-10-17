<?php

namespace Emmanix2002\Notifier\Message;

class SesEmailMessage extends EmailMessage
{
    /**
     * Returns an instance of SesEmailMessage from the provided EmailMessage instance.
     *
     * @param EmailMessage|null $instance
     *
     * @return SesEmailMessage
     */
    public static function instance(EmailMessage $instance = null): SesEmailMessage
    {
        if ($instance === null) {
            return new static();
        }
        $new = new static($instance->getFrom(false), $instance->getSubject(), null, $instance->getReplyTo());

        return $new->setHtml($instance->getBody());
    }

    /**
     * Converts a html string to plain text.
     *
     * @return string
     */
    public function toPlainText(): string
    {
        $stripped = strip_tags($this->getBody(), '<br>');
        $patterns = ['/<br[\s]*\/>/i'];

        return preg_replace($patterns, PHP_EOL, $stripped);
    }
}
