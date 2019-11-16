<?php

namespace VipPlugin\Biz\Vip\Accessor;

use Biz\Accessor\AccessorAdapter;

abstract class BaseAccessorAdapter extends AccessorAdapter
{
    protected function registerMessages()
    {
        $this->registerMessage('vip.vip_closed', '会员模块未启用');
        $this->registerMessage('vip.not_login', '用户未登录');
        $this->registerMessage('vip.not_member', '用户非会员');
        $this->registerMessage('vip.member_expired', '会员已过期');
        $this->registerMessage('vip.level_not_exist', '会员等级无效');
        $this->registerMessage('vip.level_low', '会员等级过低');
        $this->registerMessage('course.only_vip_join_way', '限会员加入');
    }

    /**
     * @return \VipPlugin\Biz\Vip\Service\VipService
     */
    protected function getVipService()
    {
        return $this->biz->service('VipPlugin:Vip:VipService');
    }
}
