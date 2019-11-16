<?php

namespace VipPlugin\Api\Resource\VipCourse;

use ApiBundle\Api\Annotation\ApiConf;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Exception\ErrorCode;
use ApiBundle\Api\Resource\AbstractResource;
use AppBundle\Common\ArrayToolkit;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use VipPlugin\Biz\Vip\Service\LevelService;

class VipCourse extends AbstractResource
{
    /**
     * @ApiConf(isRequiredAuth=false)
     */
    public function search(ApiRequest $request)
    {
        $conditions = $request->query->all();

        if (isset($conditions['levelId'])) {
            $level = $this->getLevelService()->getLevel($conditions['levelId']);
            if (empty($level)) {
                throw new AccessDeniedHttpException('levelId is not correct', null, ErrorCode::INVALID_ARGUMENT);
            }

            $enabledLevels = $this->getLevelService()->findAllLevelsLessThanSeq($level['seq']);
            $levelIds = ArrayToolkit::column($enabledLevels, 'id');

            $conditions['vipLevelIds'] = $levelIds;
            unset($conditions['levelId']);
        }

        $apiRequest = new ApiRequest('/api/courses', 'GET', $conditions);
        $result = $this->invokeResource($apiRequest);

        return $result;
    }

    /**
     * @return LevelService
     */
    private function getLevelService()
    {
        return $this->service('VipPlugin:Vip:LevelService');
    }
}
