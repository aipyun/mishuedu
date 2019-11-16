<?php

namespace VipPlugin\Slot;

use Codeages\PluginBundle\System\Slot\SlotInjection;
use VipPlugin\Biz\Vip\Service\LevelService;
use VipPlugin\Biz\Vip\Service\VipService;

class CoursePurchaseExtensionSlot extends SlotInjection
{
    public function inject()
    {

        $course = $this->course;

        if(empty($course['vipLevelId'])){
            return '';
        }

        $currentUser = $this->getCurrentUser();

        $vipMember = $this->getVipService()->getMemberByUserId($currentUser['id']);

        $userVipStatus = $this->getVipService()->checkUserInMemberLevel(
            $currentUser['id'],
            $course['vipLevelId']
        );

        $vipLevel = $this->getVipLevelService()->getLevel($course['vipLevelId']);

        return $this->container->get('twig')->render(
            'VipPlugin:Slot:course-purchase-extension-slot.html.twig',
            array(
                'vipMember' => $vipMember,
                'vipLevel' => $vipLevel,
                'userVipStatus' => $userVipStatus,
                'course' => $course,
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
