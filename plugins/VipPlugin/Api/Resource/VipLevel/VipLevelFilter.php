<?php

namespace VipPlugin\Api\Resource\VipLevel;

use ApiBundle\Api\Resource\Filter;
use ApiBundle\Api\Util\AssetHelper;
use ApiBundle\Api\Util\Money;

class VipLevelFilter extends Filter
{
    protected $publicFields = array(
        'id', 'seq', 'name', 'icon', 'monthPrice', 'yearPrice', 'description', 'freeLearned', 'enabled', 'createdTime', 'maxRate'
    );

    protected function publicFields(&$data)
    {
        $data['icon'] = empty($data['icon']) ? $this->convertFilePath('/assets/v2/img/vip/vip_icon_bronze.png') : $this->convertFilePath($data['icon']);
        $data['monthPriceConvert'] = Money::convert($data['monthPrice']);
        $data['yearPriceConvert'] = Money::convert($data['yearPrice']);

    }
}