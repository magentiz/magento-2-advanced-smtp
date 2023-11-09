<?php
namespace Magentiz\AdvancedSmtp\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;

class Data extends AbstractHelper
{
    const XML_PATH_SAVE_ATTACHMENT = 'smtp/general/save_attachment';

    public function isSaveAttachment()
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_SAVE_ATTACHMENT,
            ScopeInterface::SCOPE_STORE
        );
    }
}
