<?php
/**
 * Copyright © Magentiz. All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
 */

namespace Magentiz\AdvancedSmtp\Plugin\Email;

use Magentiz\AdvancedSmtp\Helper\Data as HelperData;
use Magentiz\AdvancedSmtp\Model\Email\AttachmentManagement;

class SaveAttachment
{
    /**
     * @var HelperData
     */
    protected $_helper;
    /**
     * @var AttachmentManagement
     */
    protected $_attachmentManagement;

    /**
     * SaveAttachment constructor.
     * @param HelperData $helper
     * @param AttachmentManagement $attachmentManagement
     */
    public function __construct(
        HelperData $helper,
        AttachmentManagement $attachmentManagement
    ) {
        $this->_helper = $helper;
        $this->_attachmentManagement = $attachmentManagement;
    }

    /**
     * @param \Mageplaza\Smtp\Model\Log $subject
     */
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
