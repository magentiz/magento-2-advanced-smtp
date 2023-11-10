<?php
/**
 * Copyright © Open Techiz. All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
 */

namespace Magentiz\AdvancedSmtp\Plugin;

class TransportBuilder
{
    const SMTP_EMAIL_TEMPLATE = 'registry_smtp_email_template';
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * TransportBuilder constructor.
     * @param \Magento\Framework\Registry $registry
     */
    public function __construct(
        \Magento\Framework\Registry $registry
    ) {
        $this->registry = $registry;
    }

    /**
     * @param \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
     * @param $templateIdentifier
     * @return array
     */
    public function beforeSetTemplateIdentifier(\Magento\Framework\Mail\Template\TransportBuilder $transportBuilder, $templateIdentifier)
    {
        $this->registry->unregister(self::SMTP_EMAIL_TEMPLATE);
        $this->registry->register(self::SMTP_EMAIL_TEMPLATE, $templateIdentifier);
        return [$templateIdentifier];
    }
}
