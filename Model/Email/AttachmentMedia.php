<?php
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

	protected $_dir;

	protected $_filesystem;

	protected $_logger;

	/**
     * @var ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

	public function __construct(
		ScopeConfigInterface $scopeConfig,
		StoreManagerInterface $storeManager,
		DirectoryList $dir,
		Filesystem $filesystem,
		LoggerInterface $logger
	)
	{
		$this->_dir = $dir;
		$this->_filesystem = $filesystem;
		$this->_logger = $logger;
		$this->_storeManager = $storeManager;
		$this->_scopeConfig = $scopeConfig;
	}

	public function save($logId = 0, \Laminas\Mime\Part $attachment)
	{
		$filename = $attachment->getFileName();
		$type = $attachment->getType();
		$content = $attachment->getRawContent();

		if(!$filename || !$content || !$logId)
		{
			return;
		}

		$path = self::FOLDER_EMAIL_LOG_ATTACHMENT.'/' . $logId . '/' . $filename;
		try{
			$media = $this->_filesystem->getDirectoryWrite($this->_dir::MEDIA);
            $media->writeFile($path,$content);
		}catch(\Throwable $e)
		{
            $this->_logger->critical($e->getMessage());
		}
	}

	public function getMediaUrl($logId = 0, $useMedia = true)
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
        $url = $this->_scopeConfig->getValue('web/unsecure/base_url', ScopeConfigInterface::SCOPE_TYPE_DEFAULT);
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
