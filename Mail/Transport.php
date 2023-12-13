<?php
/**
 * Copyright © Magentiz. All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
 */

namespace Magentiz\AdvancedSmtp\Mail;

use Laminas\Mail\Message;
use Magento\Framework\Exception\MailException;
use Magento\Framework\Mail\TransportInterface;
use Magento\Framework\Registry;
use Mageplaza\Smtp\Helper\Data;
use Mageplaza\Smtp\Mail\Rse\Mail;
use Mageplaza\Smtp\Model\Log;
use Mageplaza\Smtp\Model\LogFactory;
use Magentiz\AdvancedSmtp\Plugin\TransportBuilder;
use Psr\Log\LoggerInterface;
use Magentiz\AdvancedSmtp\Model\Config\Configuration;
use Magentiz\AdvancedSmtp\Model\Email\AttachmentManagement;

class Transport extends \Mageplaza\Smtp\Mail\Transport
{
    /**
     * @var \Magentiz\AdvancedSmtp\Mail\Transport\HttpTransport
     */
    protected $awsSes;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @var \Magento\Framework\App\State
     */
    protected $appState;

    /**
     * @var Configuration
     */
    protected $configuration;

    /**
     * @var \Magentiz\AdvancedSmtp\Model\Email\AttachmentManagement
     */
    protected $attachmentManagement;

    protected $_message = null;

    /**
     * Transport constructor.
     * @param \Magentiz\AdvancedSmtp\Mail\Transport\HttpTransport $awsSes
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Framework\App\State $appState
     * @param Configuration $configuration
     * @param Mail $resourceMail
     * @param LogFactory $logFactory
     * @param Registry $registry
     * @param Data $helper
     * @param LoggerInterface $logger
     * @param AttachmentManagement $attachmentManagement
     */
    public function __construct(
        \Magentiz\AdvancedSmtp\Mail\Transport\HttpTransport $awsSes,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\App\State $appState,
        Configuration $configuration,
        Mail $resourceMail,
        LogFactory $logFactory,
        Registry $registry,
        Data $helper,
        LoggerInterface $logger,
        AttachmentManagement $attachmentManagement
    )
    {
        $this->awsSes = $awsSes;
        $this->messageManager = $messageManager;
        $this->configuration = $configuration;
        $this->appState = $appState;
        $this->attachmentManagement = $attachmentManagement;
        parent::__construct($resourceMail, $logFactory, $registry, $helper, $logger);
    }

    /**
     * prepareMessageLog()
     * @param $subject
     * @param $message
     */
    protected function prepareMessageLog($subject, $message) {
        if ($this->helper->versionCompare('2.2.8')) {
            $messageTmp = $this->getMessage($subject);
            if ($messageTmp && is_object($messageTmp)) {
                $body = $messageTmp->getBody();
                if (is_object($body) && $body->isMultiPart()) {
                    $message->setBody($body->getPartContent('0'));
                }
            }
        }
        return $message;
    }

    /**
     * @param TransportInterface $subject
     * @param \Closure $proceed
     */
    public function aroundSendMessage(TransportInterface $subject, \Closure $proceed)
    {
        $this->_storeId = $this->registry->registry('mp_smtp_store_id');
        $message = $this->getMessage($subject);
        $this->_message = $message;
        if ($message && $this->configuration->isEnableAWSSes()) {
            if (!$this->validateBlacklist($message)) {
                $message   = $this->resourceMail->processMessage($message, $this->_storeId);
                try {
                    if (!$this->resourceMail->isDeveloperMode($this->_storeId)) {
                        if ($this->awsSes->hasAttachment($message)) {
                            $this->awsSes->sendRaw($message);
                        } else {
                            $this->awsSes->send($message);
                        }
                    }
                    if ($this->helper->versionCompare('2.2.8')) {
                        $message = Message::fromString($message->getRawMessage())->setEncoding('utf-8');
                    }
                    $message = $this->resourceMail->processMessage($message, $this->_storeId);
                    $message = $this->prepareMessageLog($subject, $message);
                    $this->emailLog($message);
                } catch (\Throwable $e) {
                    if ($this->helper->versionCompare('2.2.8')) {
                        $message = Message::fromString($message->getRawMessage())->setEncoding('utf-8');
                    }
                    $message = $this->prepareMessageLog($subject, $message);
                    $this->saveLogError($message, $e->getMessage());
                } catch (\Exception $e) {
                    if ($this->helper->versionCompare('2.2.8')) {
                        $message = Message::fromString($message->getRawMessage())->setEncoding('utf-8');
                    }
                    $message = $this->prepareMessageLog($subject, $message);
                    $this->saveLogError($message, $e->getMessage());
                }
            }
        } elseif ($this->resourceMail->isModuleEnable($this->_storeId) && $message) {
            if ($this->helper->versionCompare('2.2.8')) {
                $message = Message::fromString($message->getRawMessage())->setEncoding('utf-8');
            }

            if (!$this->validateBlacklist($message)) {
                $message   = $this->resourceMail->processMessage($message, $this->_storeId);
                $transport = $this->resourceMail->getTransport($this->_storeId);
                try {
                    if (!$this->resourceMail->isDeveloperMode($this->_storeId)) {
                        if ($this->helper->versionCompare('2.3.3')) {
                            $message->getHeaders()->removeHeader('Content-Disposition');
                        }
                        $transport->send($message);
                    }
                    $message = $this->prepareMessageLog($subject, $message);
                    $this->emailLog($message);
                } catch (\Throwable $e) {
                    $message = $this->prepareMessageLog($subject, $message);
                    $this->saveLogError($message, $e->getMessage());
                } catch (\Exception $e) {
                    $message = $this->prepareMessageLog($subject, $message);
                    $this->saveLogError($message, $e->getMessage());
                }
            }
        } else {
            $proceed();
        }
    }

    /**
     * @param $message
     * @param $error
     */
    protected function saveLogError($message, $error)
    {
        if ($this->appState === \Magento\Framework\App\Area::AREA_ADMINHTML) {
            $this->messageManager->addErrorMessage($error);
        } else {
            $this->messageManager->addErrorMessage(__('Can\'t send email'));
        }
        if ($this->resourceMail->isEnableEmailLog($this->_storeId)) {
            $log = $this->logFactory->create();
            try {
                $emailTemplate = $this->registry->registry(TransportBuilder::SMTP_EMAIL_TEMPLATE);
                $log->setEmailTemplate($emailTemplate);
                $log->setErrorMessage($error);
                $this->addMessageAttachmentToEmailLog($this->_message, $log);
                $log->saveLog($message, false);
            } catch (\Throwable $e) {
                $this->logger->critical($e->getMessage());
            } catch (\Exception $e) {
                $this->logger->critical($e->getMessage());
            }
        }
    }

    /**
     * @param $message
     * @param bool $status
     */
    protected function emailLog($message, $status = true)
    {
        if ($this->resourceMail->isEnableEmailLog($this->_storeId)) {
            /** @var Log $log */
            $log = $this->logFactory->create();
            try {
                $emailTemplate = $this->registry->registry(TransportBuilder::SMTP_EMAIL_TEMPLATE);
                $log->setEmailTemplate($emailTemplate);
                $this->addMessageAttachmentToEmailLog($this->_message, $log);
                $log->saveLog($message, $status);
                if ($status) {
                    $this->saveLogIdForAbandonedCart($log);
                }
            } catch (\Throwable $e) {
                $this->logger->critical($e->getMessage());
            } catch (\Exception $e) {
                $this->logger->critical($e->getMessage());
            }
        }
    }

    /**
     * @param $message
     * @param $log
     */
    protected function addMessageAttachmentToEmailLog($message, $log)
    {
        if(!$message || !is_object($message) || !$this->awsSes->hasAttachment($message)){
            return;
        }
        $this->attachmentManagement->addLog($message, $log);
    }
}
