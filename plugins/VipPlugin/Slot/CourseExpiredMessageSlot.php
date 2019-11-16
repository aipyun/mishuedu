<?php

namespace VipPlugin\Slot;

use Codeages\PluginBundle\System\Slot\SlotInjection;
use VipPlugin\Biz\Vip\Service\LevelService;
use VipPlugin\Biz\Vip\Service\VipService;

class CourseExpiredMessageSlot extends SlotInjection
{
    public function inject()
    {

        $course = $this->course;

        $currentUser = $this->getCurrentUser();

        $userVipStatus = $this->getVipService()->checkUserInMemberLevel(
            $currentUser['id'],
            $course['vipLevelId']
        );

        $vipLevel = $this->getVipLevelService()->getLevel($course['vipLevelId']);

        $vipMember = $this->getVipService()->getMemberByUserId($currentUser['id']);
        $currentVipLevel = $this->getVipLevelService()->getLevel($vipMember['levelId']);

        return $this->container->get('twig')->render(
            'VipPlugin:Slot:course-expired-message-slot.html.twig',
            array(
                'vipStatus' => $userVipStatus,
                'course' => $course,
                'vipLevel' => $vipLevel,
                'currentVipLevel' => $currentVipLevel,
            )
        );
    }

    /**
     * @return LevelService
     */
    protected function getVipLevelService()
    {
        return $this->container->get('biz')->service('VipPlugin:Vip:LevelService');
    }

    /**
     * @return VipService
     */
    protected function getVipService()
    {
        return $this->container->get('biz')->service('VipPlugin:Vip:VipService');
    }

    private function getCurrentUser()
    {
        $biz = $this->container->get('biz');

        return $biz['user'];
    }
}
