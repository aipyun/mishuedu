<?php

namespace VipPlugin\Biz\Vip\Accessor;

class JoinCourseVipAccessor extends BaseAccessorAdapter
{
    public function access($course)
    {
        if ($this->onlyVipJoin($course)) {
            $user = $this->getCurrentUser();
            if ('ok' === $this->getVipService()->checkUserInMemberLevel($user['id'], $course['vipLevelId'])) {
                return null;
            } else {
                return $this->buildResult('course.only_vip_join_way');
            }
        }

        return empty($course[self::CONTEXT_ERROR_KEY]) ? null : $course[self::CONTEXT_ERROR_KEY];
    }

    public function onlyVipJoin($course)
    {
        return $this->hasError($course,'course.not_buyable') && $course['vipLevelId'] > 0;
    }
}
