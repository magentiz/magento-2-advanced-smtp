<?php
/**
 * Copyright © Magentiz. All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
 */

namespace Magentiz\AdvancedSmtp\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magentiz\AdvancedSmtp\Model\Json;
use Magentiz\AdvancedSmtp\Model\Email\AttachmentMedia;

class Attachment extends \Magento\Ui\Component\Listing\Columns\Column
{
    /**
     * @var Json
     */
    protected $_json;
    /**
     * @var AttachmentMedia
     */
    protected $_attachmentMedia;

    /**
     * Attachment constructor.
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param Json $json
     * @param AttachmentMedia $attachmentMedia
     * @param array $components
     * @param array $data
     */
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

    /**
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource) {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $attachmentValue = $item['attachment'] ?? null;
                if ($attachmentValue) {
                    $item['attachment'] = $this->prepareAttachment($item, $attachmentValue);
                }
            }
        }
        return $dataSource;
    }

    /**
     * @param $item
     * @param string $attachmentValue
     * @return string
     */
    private function prepareAttachment($item, $attachmentValue = '')
    {
        $isSaveAttachment = $item['is_save_attachment'] ?? 0;
        $itemId = $item['id'] ?? null;
        $infos = $this->_json->unserialize($attachmentValue);
        $result = [];
        if ($infos) {
            foreach ($infos as $info) {
                $filename = $info['filename'] ?? null;
                if (!$filename) {
                    continue;
                }
                $result[] = (($isSaveAttachment && $itemId) ? $this->prepareFileName($itemId, $filename) : $filename);
            }
        }
        return implode('<br/>', $result);
    }

    /**
     * @param $itemId
     * @param string $filename
     * @return string
     */
    private function prepareFileName($itemId, $filename = '')
    {
        try {
            $mediaPath = $this->_attachmentMedia->getMediaUrl($itemId);
            return '<a target="_blank" href="'. $mediaPath . $filename .'">'.$filename.'</a>';
        } catch(\Throwable $e) {

        }
        return $filename;
    }
}
