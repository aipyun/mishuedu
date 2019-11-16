<?php

namespace VipPlugin\Biz\Vip\Accessor;

use Biz\Course\Service\MemberService;

class LearnCourseVipAccessor extends BaseAccessorAdapter
{
    public function access($course)
    {
        if ($course['vipLevelId'] <= 0) {
            return null;
        }
        $user = $this->getCurrentUser();
        $member = $this->getMemberService()->getCourseMember($course['id'], $user['id']);
        if ($member['role'] === 'teacher') {
            return null;
        }

        if ($member['levelId'] == 0) {
            return null;
        }

        $userVipStatus = $this->getVipService()->checkUserInMemberLevel(
            $user['id'],
            $course['vipLevelId']
        );

        if ($userVipStatus !== 'ok') {
            return $this->buildResult('vip.'.$userVipStatus);
        }

        return null;
    }

    /**
     * @return MemberService
     */
    protected function getMemberService()
    {
        return $this->biz->service('Course:MemberService');
    }
}
