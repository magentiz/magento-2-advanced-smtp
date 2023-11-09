<?php

namespace Magentiz\AdvancedSmtp\Plugin\Email;

use Magentiz\AdvancedSmtp\Helper\Data as HelperData;
use Magentiz\AdvancedSmtp\Model\Email\AttachmentManagement;

class SaveAttachment
{

    protected $_helper;

    protected $_attachmentManagement;

    public function __construct(
        HelperData $helper,
        AttachmentManagement $attachmentManagement
    ) {
        $this->_helper = $helper;
        $this->_attachmentManagement = $attachmentManagement;
    }

    public function afterSave(\Mageplaza\Smtp\Model\Log $subject)
    {
        if ($this->_helper->isSaveAttachment() && $subject->getId() && $subject->getAttachmentParts()) {
            try {
                $this->_attachmentManagement->saveAttachments($subject);
            } catch (\Throwable $e) {
            } catch (\Exception $e) {
            }
        }
    }
}
