<?php

namespace VipPlugin\Biz\Vip\Accessor;

class JoinClassroomVipAccessor extends BaseAccessorAdapter
{
    public function access($classroom)
    {
        if ($this->onlyVipJoin($classroom)) {
            $user = $this->getCurrentUser();
            if ('ok' === $this->getVipService()->checkUserInMemberLevel($user['id'], $classroom['vipLevelId'])) {
                return null;
            } else {
                return $this->buildResult('course.only_vip_join_way');
            }
        }

        return empty($classroom[self::CONTEXT_ERROR_KEY]) ? null : $classroom[self::CONTEXT_ERROR_KEY];
    }

    public function onlyVipJoin($classroom)
    {
        return $this->hasError($classroom,'classroom.not_buyable') && $classroom['vipLevelId'] > 0;
    }
}
