<?php

namespace Magentiz\AWSSes\Mail\Transport;

use Laminas\Mail\Transport\TransportInterface;
use Laminas\Mail\Message;

class HttpTransport implements TransportInterface
{
    public function __construct(\Magentiz\AWSSes\Mail\SesService $service)
    {
        $this->service = $service;
    }

    /**
     * {@inheritDoc}
     */
    public function send($message)
    {
        $this->service->send($message);
    }

    public function hasAttachment($message){
        $body = $message->getBody();
        $filter      = ['text/plain', 'text/html'];
        foreach ($body->getParts() as $part) {
            if (!in_array($part->getType(), $filter)) {
                return true;       
            }
        }
        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function sendRaw($message)
    {
        $this->service->sendRaw($message);
    }
}