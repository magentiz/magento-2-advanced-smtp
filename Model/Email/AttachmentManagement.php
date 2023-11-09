<?php
namespace Magentiz\AdvancedSmtp\Model\Email;

use Magentiz\AdvancedSmtp\Helper\Data as HelperData;
use Psr\Log\LoggerInterface;
use Magentiz\AdvancedSmtp\Model\Json;

class AttachmentManagement
{

	const SAVE_ATTACHMENT_ENABLED = 1;

	protected $_helper;

	protected $_logger;

	protected $_json;

	protected $_attachmentMedia;

	public function __construct(
		HelperData $helper,
		LoggerInterface $logger,
		Json $json,
		AttachmentMedia $attachmentMedia
	)
	{
		$this->_helper = $helper;
		$this->_logger = $logger;
		$this->_json = $json;
		$this->_attachmentMedia = $attachmentMedia;
	}


	public function addLog($message, \Mageplaza\Smtp\Model\Log $log)
	{
		$attachments = $this->getAttachments($message);
		if(!$attachments)
		{
			return;
		}

		$info = [];
		foreach($attachments as $attachment)
		{
			if(!$attachment->getFileName())
			{
				continue;
			}
			$info[] = ['filename' => $attachment->getFileName()];
		}

		if($this->_helper->isSaveAttachment())
		{
			$log->setIsSaveAttachment(self::SAVE_ATTACHMENT_ENABLED);
			$log->setAttachmentParts($attachments);
		}
		$log->setAttachment($this->_json->serialize($info));
	}

	protected function getAttachments($message)
	{
		$body = $message->getBody();
        $parts = $body && method_exists($body, 'getParts') ? $body->getParts() : [];
        $filter = ['text/plain', 'text/html'];
        $attachments = [];
        foreach($parts as $part)
        {
            if (!in_array($part->getType(), $filter)) {
                $attachments[] = $part;
            }
        }
        return $attachments;
	}

	public function saveAttachments(\Mageplaza\Smtp\Model\Log $log)
	{
		$attachments = $log->getAttachmentParts();
		$logId = $log->getId();
		if(!$logId || !$attachments)
		{
			return;
		}

		foreach($attachments as $attachment)
		{
			$this->_attachmentMedia->save($logId, $attachment);
		}
	}
}
