<?php

namespace VipPlugin\Api\Resource\VipLevel;

use ApiBundle\Api\Annotation\ApiConf;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;

class VipLevel extends AbstractResource
{
    /**
     * @ApiConf(isRequiredAuth=false)
     */
    public function search(ApiRequest $request)
    {
        return $this->service('VipPlugin:Vip:LevelService')->findEnabledLevels();
    }

    /**
     * @ApiConf(isRequiredAuth=false)
     */
    public function get(ApiRequest $request, $levelId)
    {
        return $this->service('VipPlugin:Vip:LevelService')->getLevel($levelId);
    }
}