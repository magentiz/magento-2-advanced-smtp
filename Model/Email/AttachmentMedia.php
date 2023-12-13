<?php
/**
 * Copyright © Magentiz. All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
 */

namespace Magentiz\AdvancedSmtp\Model\Email;

use Psr\Log\LoggerInterface;
use Magento\Framework\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\UrlInterface;

class AttachmentMedia
{
	const FOLDER_EMAIL_LOG_ATTACHMENT = 'attachment_emailog';
	const UNSECURE_BASE_URL = 'web/unsecure/base_url';
    /**
     * @var ScopeConfigInterface
     */
    protected $_scopeConfig;
    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;
    /**
     * @var DirectoryList
     */
    protected $_dir;
    /**
     * @var Filesystem
     */
    protected $_filesystem;
    /**
     * @var LoggerInterface
     */
    protected $_logger;

    /**
     * AttachmentMedia constructor.
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     * @param DirectoryList $dir
     * @param Filesystem $filesystem
     * @param LoggerInterface $logger
     */
    public function __construct(
		ScopeConfigInterface $scopeConfig,
		StoreManagerInterface $storeManager,
		DirectoryList $dir,
		Filesystem $filesystem,
		LoggerInterface $logger
	)
	{
        $this->_scopeConfig = $scopeConfig;
        $this->_storeManager = $storeManager;
		$this->_dir = $dir;
		$this->_filesystem = $filesystem;
		$this->_logger = $logger;
	}

    /**
     * @param int $logId
     * @param \Laminas\Mime\Part $attachment
     */
    public function save(\Laminas\Mime\Part $attachment, $logId = 0)
	{
		$filename = $attachment->getFileName();
		$content = $attachment->getRawContent();
		if (!$filename || !$content || !$logId) {
			return;
		}

		$path = self::FOLDER_EMAIL_LOG_ATTACHMENT.'/' . $logId . '/' . $filename;
		try {
			$media = $this->_filesystem->getDirectoryWrite($this->_dir::MEDIA);
            $media->writeFile($path,$content);
		} catch (\Throwable $e) {
            $this->_logger->critical($e->getMessage());
		}
	}

    /**
     * @param int $logId
     * @param bool $useMedia
     * @return string
     */
    public function getMediaUrl($useMedia = true, $logId = 0)
	{
		$mediaUrl = ($useMedia)
        ? $this->_storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA)
        : $this->getCurrentDomain().'media/';

        return $mediaUrl . self::FOLDER_EMAIL_LOG_ATTACHMENT . '/' . $logId . '/';
	}

	/**
     * Get Current Domain not sub store
     */
    private function getCurrentDomain()
    {
        $url = $this->_scopeConfig->getValue(self::UNSECURE_BASE_URL, ScopeConfigInterface::SCOPE_TYPE_DEFAULT);
        if (!$url) {
            $currentStore = $this->_storeManager->getStore();
            $url = $currentStore->getCurrentUrl();
        }
        $domain = '';
        $parse = parse_url($url);
        if (isset($parse['scheme']) && isset($parse['host'])) {
            $domain = $parse['scheme'] . '://' . $parse['host'].'/';
        }
        return $domain;
    }
}
