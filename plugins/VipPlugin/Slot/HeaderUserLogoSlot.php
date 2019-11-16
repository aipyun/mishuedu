<?php

namespace VipPlugin\Slot;

use Codeages\PluginBundle\System\Slot\SlotInjection;

class HeaderUserLogoSlot extends SlotInjection
{
    public function inject()
    {
        $user = $this->user;
        $member = $this->getVipService()->getMemberByUserId($user['id']);
        $level = empty($member) ? array() : $this->getVipLevelService()->getLevel($member['levelId']);

        return $this->container->get('twig')->render('VipPlugin:Slot:header-user-logo.html.twig', array(
            'level' => $level,
            'member' => $member
        ));
    }

    protected function getVipLevelService()
    {
        return $this->container->get('biz')->service('VipPlugin:Vip:LevelService');
    }

    protected function getVipService()
    {
        return $this->container->get('biz')->service('VipPlugin:Vip:VipService');
    }
}
