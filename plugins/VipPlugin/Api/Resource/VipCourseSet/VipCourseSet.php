<?php

namespace VipPlugin\Api\Resource\VipCourseSet;

use ApiBundle\Api\Annotation\ApiConf;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Exception\ErrorCode;
use ApiBundle\Api\Resource\AbstractResource;
use AppBundle\Common\ArrayToolkit;
use Biz\Course\Service\CourseService;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use VipPlugin\Biz\Vip\Service\LevelService;

class VipCourseSet extends AbstractResource
{
    /**
     * @ApiConf(isRequiredAuth=false)
     */
    public function search(ApiRequest $request)
    {
        $conditions = $request->query->all();
        $conditions['isVip'] = 1;
        if (isset($conditions['levelId'])) {
            $level = $this->getLevelService()->getLevel($conditions['levelId']);
            if (empty($level)) {
                throw new AccessDeniedHttpException('levelId is not correct', null, ErrorCode::INVALID_ARGUMENT);
            }

            $enabledLevels = $this->getLevelService()->findAllLevelsLessThanSeq($level['seq']);
            $levelIds = ArrayToolkit::column($enabledLevels, 'id');

            $conditions['vipLevelIds'] = $levelIds;
            $courses = $this->getCourseService()->searchCourses(
                array(
                    'vipLevelIds' => $levelIds,
                ),
                'latest',
                0,
                PHP_INT_MAX,
                array('courseSetId')
            );
            if (!empty($courses)) {
                $conditions['ids'] = ArrayToolkit::column($courses, 'courseSetId');
            }
        }

        $apiRequest = new ApiRequest('/api/course_sets', 'GET', $conditions);
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

    /**
     * @return CourseService
     */
    private function getCourseService()
    {
        return $this->service('Course:CourseService');
    }
}