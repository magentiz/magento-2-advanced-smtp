<?php
/**
 * Copyright © Magentiz. All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
 */

namespace Magentiz\AdvancedSmtp\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;

class Data extends AbstractHelper
{
    const XML_PATH_SAVE_ATTACHMENT = 'smtp/general/save_attachment';

    /**
     * Check if save attachment or not
     * @return mixed
     */
    public function isSaveAttachment()
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_SAVE_ATTACHMENT,
            ScopeInterface::SCOPE_STORE
        );
    }
}
