<?php

namespace Emmanix2002\Notifier\Recipient;

interface RecipientInterface
{
    /**
     * This returns the address for this recipient. This address can be anything:
     * email, phone number, array -- all that matters is that it can be used to identify a destination
     * for the handler
     *
     * @return mixed
     */
    public function getAddress();
}