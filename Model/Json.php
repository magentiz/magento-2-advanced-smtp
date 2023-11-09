<?php
namespace Magentiz\AdvancedSmtp\Model;

use Magento\Framework\Serialize\Serializer\Json as MagentoJson;

class Json
{
	protected $_json;

	public function __construct(
		MagentoJson $json
	)
	{
		$this->_json = $json;
	}

	public function serialize($data)
	{
		return $this->_json->serialize($data);
	}

	public function unserialize($string)
	{
		return $this->_json->unserialize($string);
	}
}
