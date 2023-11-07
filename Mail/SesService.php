<?php

namespace Magentiz\AWSSes\Mail;

use Magentiz\AWSSes\Model\Config\Configuration;
use Magentiz\AWSSes\Service\AbstractMailService;
use Aws\Ses\Exception\SesException;
use Aws\Ses\SesClient;
use Laminas\Mail\Message;
use Laminas\Mail\Exception\RuntimeException;
use Laminas\Mail\Exception\OutOfBoundsException;
use Laminas\Mail\Exception\InvalidArgumentException;
use Laminas\Mail\Exception\DomainException;
use Laminas\Mail\Exception\BadMethodCallException;

class SesService extends AbstractMailService
{
    /**
     * SES supports a maximum of 50 recipients per messages
     */
    public const RECIPIENT_LIMIT = 500;

    public function __construct(Configuration $configuration)
    {
        $this->configuration = $configuration;
    }
    /**
     * {@inheritDoc}
     * @link http://help.postageapp.com/kb/api/send_message
     * @throws Exception\RuntimeException if the mail is sent to more than 50 recipients (Amazon SES limit)
     * @return array The id and UID of the sent message (if sent correctly)
     */
    public function send($message)
    {
        $SesClient = SesClient::factory(array(
            'credentials' => array(
                'key'    => $this->configuration->getApiKey(),
                'secret' => $this->configuration->getSecretKey(),
            ),
            'region' => 'eu-west-1',
            'version' => 'latest'
        ));

        $from = $message->getFrom();
        $from_name = base64_encode($from[0]->getName());
        $from_en = "=?utf-8?B?$from_name?=";

        $parameters = [
            'Source'  =>  $from_en . ' <' . $from[0]->getEmail() . '>',
            'Message' => [
                'Subject' => ['Data' => $message->getSubject()],
            ]
        ];

        $textContent = $this->extractText($message);
        if (!empty($textContent)) {
            $parameters['Message']['Body']['Text']['Data'] = $textContent;
        }

        $htmlContent = $this->extractHtml($message);
        if (!empty($htmlContent)) {
            $parameters['Message']['Body']['Html']['Data'] = $htmlContent;
        }

        $countRecipients = count($message->getTo());

        $to = [];
        foreach ($message->getTo() as $address) {
            $to[] = $address->getEmail();
        }

        $parameters['Destination']['ToAddresses'] = $to;

        $countRecipients += count($message->getCc());

        $cc = [];
        foreach ($message->getCc() as $address) {
            $cc[] = $address->getEmail();
        }

        $parameters['Destination']['CcAddresses'] = $cc;

        $countRecipients += count($message->getBcc());

        $bcc = [];
        foreach ($message->getBcc() as $address) {
            $bcc[] = $address->getEmail();
        }

        $parameters['Destination']['BccAddresses'] = $bcc;
        $replyTo = [];
        foreach ($message->getReplyTo() as $address) {
            $replyTo[] = $address->getEmail();
        }

        $parameters['ReplyToAddresses'] = $replyTo;
        return $SesClient->sendEmail($this->filterParameters($parameters))->toArray();
    }

    public function sendRaw($message)
    {
        $SesClient = SesClient::factory(array(
            'credentials' => array(
                'key'    => $this->configuration->getApiKey(),
                'secret' => $this->configuration->getSecretKey(),
            ),
            'region' => 'eu-west-1',
            'version' => 'latest'
        ));

        $attachments = $this->extractAttachments($message);

        $to = [];
        foreach ($message->getTo() as $address) {
            $to[] = $address->getEmail();
        }
        $from = $message->getFrom();
        $htmlContent = $this->extractHtml($message);

        $mySeparator = md5(time());
        $mySeparator_multipart = md5($message->getSubject() . time());

        $myMessage = "";

        $myMessage .= "MIME-Version: 1.0\n";

        $myMessage .= "To: ". $to[0] ."\n";

        $myMessage .= "From:". $from[0]->getName() . ' <' . $from[0]->getEmail() . '>' ."\n";
        $myMessage .= "Subject:".$message->getSubject()."\n";

        $myMessage .= "Content-Type: multipart/mixed; boundary=\"".$mySeparator_multipart."\"\n";
        $myMessage .= "\n--".$mySeparator_multipart."\n";

        $myMessage .= "Content-Type: multipart/alternative; boundary=\"".$mySeparator."\"\n";
        $myMessage .= "\n--".$mySeparator."\n";

        $myMessage .= "Content-Type: text/html; charset=\"UTF-8\"\n";
        $myMessage .= "\n".$htmlContent."\n";
        $myMessage .= "\n--".$mySeparator."--\n";

        foreach($attachments as $attachment) {
            $fileName = $attachment->getFileName();
            $contentAtt = $attachment->getContent();
            $type = $attachment->getType();
            $myMessage .= "--" . $mySeparator_multipart . "\n";
            $myMessage .= "Content-Type: " . $type . "; name=\"" . $fileName . "\"\n";
            $myMessage .= "Content-Disposition: attachment; filename=\"" . $fileName . "\"\n";
            $myMessage .= "Content-Transfer-Encoding: base64\n\n";
            $myMessage .= $contentAtt . "\n";
        }
        $myMessage .= "--" . $mySeparator_multipart . "--";
        $myArraySES = [
            'Source'       => $from[0]->getEmail(),
            'Destinations' => $to,
            'RawMessage'   => [
                'Data' => $myMessage
            ]
        ];

        return $SesClient->sendRawEmail($myArraySES);
    }

    /**
     * Get the user's current sending limits
     *
     * @link http://docs.aws.amazon.com/ses/latest/APIReference/API_GetSendQuota.html
     * @return array
     */
    public function getSendQuota()
    {
        try {
            return $this->client->getSendQuota()->toArray();
        } catch (SesException $exception) {
            $this->parseException($exception);
        }
    }

    /**
     * Get the user's sending statistics. The result is a list of data points, representing the last two weeks
     * of sending activity
     *
     * @link http://docs.aws.amazon.com/ses/latest/APIReference/API_GetSendStatistics.html
     * @return array
     */
    public function getSendStatistics()
    {
        try {
            return $this->client->getSendStatistics()->toArray();
        } catch (SesException $exception) {
            $this->parseException($exception);
        }
    }

    /**
     * ------------------------------------------------------------------------------------------
     * IDENTITIES AND EMAILS
     * ------------------------------------------------------------------------------------------
     */

    /**
     * Get a list containing all of the identities (email addresses and domains) for a specific AWS Account,
     * regardless of verification status
     *
     * @link http://docs.aws.amazon.com/ses/latest/APIReference/API_ListIdentities.html
     * @param  string $identityType can be EmailAddress or Domain
     * @param  int    $maxItems can be between 1 and 100 inclusive
     * @param  string $nextToken token to use for pagination
     * @return array
     */
    public function getIdentities(string $identityType = '', int $maxItems = 50, string $nextToken = '')
    {
        $parameters = [
            'IdentityType' => $identityType,
            'MaxItems'     => $maxItems,
            'NextToken'    => $nextToken
        ];

        try {
            return $this->client->listIdentities(array_filter($parameters))->toArray();
        } catch (SesException $exception) {
            $this->parseException($exception);
        }
    }

    /**
     * Delete the specified identity (email address or domain) from the list of verified identities
     *
     * @link http://docs.aws.amazon.com/ses/latest/APIReference/API_DeleteIdentity.html
     * @param  string $identity
     * @return void
     */
    public function deleteIdentity(string $identity)
    {
        try {
            $this->client->deleteIdentity(['Identity' => $identity]);
        } catch (SesException $exception) {
            $this->parseException($exception);
        }
    }

    /**
     * Get the current status of Easy DKIM signing for an entity
     *
     * @link http://docs.aws.amazon.com/ses/latest/APIReference/API_GetIdentityDkimAttributes.html
     * @param  array $identities
     * @return array
     */
    public function getIdentityDkimAttributes(array $identities)
    {
        try {
            return $this->client->getIdentityDkimAttributes(['Identities' => $identities])->toArray();
        } catch (SesException $exception) {
            $this->parseException($exception);
        }
    }

    /**
     * Given a list of verified identities (email addresses and/or domains), returns a structure describing
     * identity notification attributes
     *
     * @link http://docs.aws.amazon.com/ses/latest/APIReference/API_GetIdentityNotificationAttributes.html
     * @param  array $identities
     * @return array
     */
    public function getIdentityNotificationAttributes(array $identities)
    {
        try {
            return $this->client->getIdentityNotificationAttributes(['Identities' => $identities])->toArray();
        } catch (SesException $exception) {
            $this->parseException($exception);
        }
    }

    /**
     * Given a list of identities (email addresses and/or domains), returns the verification status and (for domain
     * identities) the verification token for each identity
     *
     * @link http://docs.aws.amazon.com/ses/latest/APIReference/API_GetIdentityVerificationAttributes.html
     * @param  array $identities
     * @return array
     */
    public function getIdentityVerificationAttributes(array $identities)
    {
        try {
            return $this->client->getIdentityVerificationAttributes(['Identities' => $identities])->toArray();
        } catch (SesException $exception) {
            $this->parseException($exception);
        }
    }

    /**
     * Enable or disable Easy DKIM signing of email sent from an identity
     *
     * @link http://docs.aws.amazon.com/ses/latest/APIReference/API_SetIdentityDkimEnabled.html
     * @param  string $identity
     * @param  bool $dkimEnabled
     * @return void
     */
    public function setIdentityDkimEnabled(string $identity, bool $dkimEnabled): void
    {
        try {
            $this->client->setIdentityDkimEnabled([
                'Identity'    => $identity,
                'DkimEnabled' => (bool) $dkimEnabled
            ]);
        } catch (SesException $exception) {
            $this->parseException($exception);
        }
    }

    /**
     * Given an identity (email address or domain), enables or disables whether Amazon SES forwards feedback
     * notifications as email
     *
     * @link http://docs.aws.amazon.com/ses/latest/APIReference/API_SetIdentityFeedbackForwardingEnabled.html
     * @param  string $identity
     * @param  bool $forwardingEnabled
     * @return void
     */
    public function setIdentityFeedbackForwardingEnabled(string $identity, bool $forwardingEnabled): void
    {
        try {
            $this->client->setIdentityFeedbackForwardingEnabled([
                'Identity'          => $identity,
                'ForwardingEnabled' => (bool) $forwardingEnabled
            ]);
        } catch (SesException $exception) {
            $this->parseException($exception);
        }
    }

    /**
     * Given an identity (email address or domain), sets the Amazon SNS topic to which Amazon SES will publish bounce
     * and complaint notifications for emails sent with that identity as the Source
     *
     * @link http://docs.aws.amazon.com/ses/latest/APIReference/API_SetIdentityNotificationTopic.html
     * @param string $identity
     * @param string $notificationType
     * @param string $snsTopic
     * @return void
     */
    public function setIdentityNotificationTopic(string $identity, string $notificationType, string $snsTopic = ''): void
    {
        $parameters = [
            'Identity'         => $identity,
            'NotificationType' => $notificationType,
            'SnsTopic'         => $snsTopic
        ];

        try {
            $this->client->setIdentityNotificationTopic(array_filter($parameters));
        } catch (SesException $exception) {
            $this->parseException($exception);
        }
    }

    /**
     * Get a set of DKIM tokens for a domain
     *
     * DKIM tokens are character strings that represent your domain's identity. Using these tokens, you will need to
     * create DNS CNAME records that point to DKIM public keys hosted by Amazon SES. This action is throttled at
     * one request per second.
     *
     * @link http://docs.aws.amazon.com/ses/latest/APIReference/API_VerifyDomainDkim.html
     * @param  string $domain
     * @return array
     */
    public function verifyDomainDkim(string $domain)
    {
        try {
            return $this->client->verifyDomainDkim(['Domain' => $domain])->toArray();
        } catch (SesException $exception) {
            $this->parseException($exception);
        }
    }

    /**
     * Verifies a domain identity
     *
     * @link http://docs.aws.amazon.com/ses/latest/APIReference/API_VerifyDomainIdentity.html
     * @param string $domain
     * @return void
     */
    public function verifyDomainIdentity(string $domain): void
    {
        try {
            $this->client->verifyDomainIdentity(['Domain' => $domain]);
        } catch (SesException $exception) {
            $this->parseException($exception);
        }
    }

    /**
     * Verify an email address. This action causes a confirmation email message to be sent to the specified address
     *
     * @link http://docs.aws.amazon.com/ses/latest/APIReference/API_VerifyEmailIdentity.html
     * @param  string $email
     * @return void
     */
    public function verifyEmailIdentity(string $email): void
    {
        try {
            $this->client->verifyEmailIdentity(['EmailAddress' => $email]);
        } catch (SesException $exception) {
            $this->parseException($exception);
        }
    }

    /**
     * @param  SesException $exception
     * @throws \RuntimeException
     */
    private function parseException(SesException $exception)
    {
        switch ($exception->getStatusCode()) {
            case 400:
                throw new \RuntimeException(sprintf(
                    'An error occurred on Amazon SES (code %s): %s',
                    $exception->getStatusCode(),
                    $exception->getMessage()
                ));
            case 403:
                throw new \RuntimeException(sprintf(
                    'Amazon SES authentication error (code %s): %s',
                    $exception->getStatusCode(),
                    $exception->getMessage()
                ));
            default:
                throw new \RuntimeException(sprintf(
                    'An error occurred on Amazon SES (code %s): %s',
                    $exception->getStatusCode(),
                    $exception->getMessage()
                ));
        }
    }
}
