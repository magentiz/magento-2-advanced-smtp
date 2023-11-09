<?php
namespace Magentiz\AdvancedSmtp\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magentiz\AdvancedSmtp\Model\Json;
use Magentiz\AdvancedSmtp\Model\Email\AttachmentMedia;

class Attachment extends \Magento\Ui\Component\Listing\Columns\Column
{

    protected $_json;

    protected $_attachmentMedia;

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        Json $json,
        AttachmentMedia $attachmentMedia,
        array $components = [],
        array $data = []
    ){
        $this->_json = $json;
        $this->_attachmentMedia = $attachmentMedia;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepareDataSource(array $dataSource) {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $attachmentValue = $item['attachment'] ?? null;
                if($attachmentValue)
                {
                    $item['attachment'] = $this->prepareAttachment($item, $attachmentValue);
                }
            }
        }
        return $dataSource;
    }

    private function prepareAttachment($item, $attachmentValue = '')
    {
        $isSaveAttachment = $item["is_save_attachment"] ?? 0;
        $itemId = $item["id"] ?? null;
        $infos = $this->_json->unserialize($attachmentValue);
        $result = [];
        if($infos)
        {
            foreach($infos as $info)
            {
                $filename = $info['filename'] ?? null;
                if(!$filename)
                {
                    continue;
                }
                $result[] = (($isSaveAttachment && $itemId) ? $this->prepareFileName($itemId, $filename) : $filename);
            }
        }
        return implode("<br/>", $result);
    }

    private function prepareFileName($itemId, $filename = '')
    {
        try{
            $mediaPath = $this->_attachmentMedia->getMediaUrl($itemId);
            return '<a target="_blank" href="'. $mediaPath . $filename .'">'.$filename.'</a>';
        }catch(\Throwable $e)
        {

        }
        return $filename;
    }
}
