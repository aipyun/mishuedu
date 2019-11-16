<?php

namespace VipPlugin\Biz\Vip\Accessor;

use Biz\Classroom\Service\ClassroomService;

class LearnClassroomVipAccessor extends BaseAccessorAdapter
{
    public function access($classroom)
    {
        if ($classroom['vipLevelId'] <= 0) {
            return null;
        }

        $user = $this->getCurrentUser();

        $member = $this->getClassroomService()->getClassroomMember($classroom['id'], $user['id']);

        if (array_intersect($member['role'], array('assistant', 'teacher', 'headTeacher'))) {
            return null;
        }

        if ($member['levelId'] == 0) {
            return null;
        }

        $userVipStatus = $this->getVipService()->checkUserInMemberLevel(
            $user['id'],
            $classroom['vipLevelId']
        );

        if ($userVipStatus !== 'ok') {
            return $this->buildResult('vip.'.$userVipStatus);
        }

        return null;
    }

    /**
     * @return ClassroomService
     */
    private function getClassroomService()
    {
        return $this->biz->service('Classroom:ClassroomService');
    }
}
