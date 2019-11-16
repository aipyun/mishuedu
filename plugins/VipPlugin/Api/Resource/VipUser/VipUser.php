<?php

namespace VipPlugin\Api\Resource\VipUser;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Exception\ErrorCode;
use ApiBundle\Api\Resource\AbstractResource;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use VipPlugin\Biz\Vip\Service\LevelService;
use VipPlugin\Biz\Vip\Service\VipService;

class VipUser extends AbstractResource
{
    public function get(ApiRequest $request, $userId)
    {
        $user = $this->getCurrentUser();
        if ($user['id'] != $userId) {
            throw new AccessDeniedHttpException('userId is not match auth', null, ErrorCode::INVALID_ARGUMENT);
        }

        $vipMember = $this->getVipService()->getMemberByUserId($userId);
        if (empty($vipMember)) {
            return null;
        }

        $vipLevel = $this->getLevelService()->getLevel($vipMember['levelId']);
        $vipMember['level'] = $vipLevel;
        return $vipMember;
    }

    /**
     * @return VipService
     */
    private function getVipService()
    {
        return $this->service('VipPlugin:Vip:VipService');
    }

    /**
     * @return LevelService
     */
    private function getLevelService()
    {
        return $this->service('VipPlugin:Vip:LevelService');
    }
}