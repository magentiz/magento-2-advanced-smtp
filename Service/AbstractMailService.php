<?php
/**
 * Copyright © Magentiz. All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
 */

namespace Magentiz\AdvancedSmtp\Service;

use Magentiz\AdvancedSmtp\Api\MailServiceInterface;
use Laminas\Http\Client as HttpClient;
use Laminas\Mail\Message;
use Laminas\Mime\Message as MimeMessage;
use Laminas\Mime\Mime;

abstract class AbstractMailService implements MailServiceInterface
{
    /**
     * @var HttpClient
     */
    protected $client;

    /**
     * Extract text part from a message
     *
     * @param  Message $message
     * @return string|null
     */
    protected function extractText($message): ?string
    {
        $body = $message->getBody();

        if (is_string($body)) {
            return $body;
        }

        if (!$body instanceof MimeMessage) {
            return null;
        }

        foreach ($body->getParts() as $part) {

            if ($part->getType() === 'text/plain') {
                return $part->getRawContent();
            }
        }

        return null;
    }

    /**
     * @param $message
     * @return string|null
     */
    protected function extractHtml($message): ?string
    {
        $body = $message->getBody();

        // If body is not a MimeMessage object, then the body is just the text version
        if (is_string($body) || !$body instanceof MimeMessage) {
            return null;
        }
        foreach ($body->getParts() as $part) {
            if ($part->getType() === 'text/html') {
                return $part->getRawContent();
            }
        }

        return null;
    }

    /**
     * Extract all attachments from a message
     *
     * Attachments are detected in the Mime message where
     * the type of the mime part is not text/plain or
     * text/html.
     *
     * @param  Message $message
     * @return \Laminas\Mime\Part[]
     */
    protected function extractAttachments($message)
    {
        $body = $message->getBody();
        $filter      = ['text/plain', 'text/html'];
        $attachments = [];
        if (!method_exists($body, 'getParts')) {
            return $attachments;
        }
        foreach ($body->getParts() as $part) {
            if (!in_array($part->getType(), $filter)) {
                $attachments[] = $part;
            }
        }

        return $attachments;
    }

    /**
     * Get HTTP client
     *
     * @return HttpClient
     */
    protected function getClient(): HttpClient
    {
        if (null === $this->client) {
            $this->setClient(new HttpClient());
        }

        return $this->client;
    }

    /**
     * Set HTTP client
     *
     * @param HttpClient $client
     * @return void
     */
    public function setClient(HttpClient $client): void
    {
        $this->client = $client;
    }

    /**
     * Filter parameters recursively (for now, only null parameters and empty strings)
     *
     * @param  array $parameters
     * @return array
     */
    protected function filterParameters(array $parameters): array
    {
        foreach ($parameters as &$value) {
            if (is_array($value)) {
                $value = $this->filterParameters($value);
            }
        }

        return array_filter($parameters, function ($value) {
            return $value !== null && $value !== '';
        });
    }
}
