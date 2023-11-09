<?php

namespace Magentiz\AdvancedSmtp\Plugin;

class TransportBuilder
{
    const SMTP_EMAIL_TEMPLATE = 'registry_smtp_email_template';

    protected $registry;

    public function __construct(
        \Magento\Framework\Registry $registry
    ) {
        $this->registry = $registry;
    }
    public function beforeSetTemplateIdentifier(\Magento\Framework\Mail\Template\TransportBuilder $transportBuilder, $templateIdentifier)
    {
        $this->registry->unregister(self::SMTP_EMAIL_TEMPLATE);
        $this->registry->register(self::SMTP_EMAIL_TEMPLATE, $templateIdentifier);
        return [$templateIdentifier];
    }
}
