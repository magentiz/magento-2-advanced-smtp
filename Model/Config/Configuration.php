<?php

namespace Magentiz\AWSSes\Model\Config;

class Configuration
{
    const ENABLE_AWS_SES_CONFIG_PATH = 'smtp/awsses/enable_aws_ses';
    const AWS_SES_API_KEY_CONFIG_PATH = 'smtp/awsses/aws_api_key';
    const AWS_SES_SECRET_KEY_CONFIG_PATH = 'smtp/awsses/aws_secret_key';

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor
    ){
        $this->_scopeConfig = $scopeConfig;
        $this->encryptor = $encryptor;
    }

    public function isEnableAWSSes(){
        return (bool) $this->_scopeConfig->getValue(self::ENABLE_AWS_SES_CONFIG_PATH);
    }
    public function getApiKey(){
        return $this->_scopeConfig->getValue(self::AWS_SES_API_KEY_CONFIG_PATH);
    }

    public function getSecretKey(){
        return $this->encryptor->decrypt($this->_scopeConfig->getValue(self::AWS_SES_SECRET_KEY_CONFIG_PATH));
    }
}
