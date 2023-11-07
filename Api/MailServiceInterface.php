<?php

namespace Magentiz\AWSSes\Api;

use Laminas\Mail\Message;

interface MailServiceInterface
{
    /**
     * Send a message
     *
     * @param  Message $message
     * @return mixed
     */
    public function send(Message $message);
}
