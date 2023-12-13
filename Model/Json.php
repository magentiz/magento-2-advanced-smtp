<?php
/**
 * Copyright © Magentiz. All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
 */

namespace Magentiz\AdvancedSmtp\Model;

use Magento\Framework\Serialize\Serializer\Json as MagentoJson;

class Json
{
    /**
     * @var MagentoJson
     */
    protected $_json;

    /**
     * Json constructor.
     * @param MagentoJson $json
     */
    public function __construct(
		MagentoJson $json
	)
	{
		$this->_json = $json;
	}

    /**
     * @param $data
     * @return mixed
     */
    public function serialize($data)
	{
		return $this->_json->serialize($data);
	}

    /**
     * @param $string
     * @return mixed
     */
    public function unserialize($string)
	{
		return $this->_json->unserialize($string);
	}
}
