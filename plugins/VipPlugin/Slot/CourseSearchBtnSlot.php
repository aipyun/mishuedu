<?php

namespace VipPlugin\Slot;

use Codeages\PluginBundle\System\Slot\SlotInjection;
use VipPlugin\Biz\Vip\Service\LevelService;
use VipPlugin\Biz\Vip\Service\VipService;

class CourseSearchBtnSlot extends SlotInjection
{
    public function inject()
    {
        $keyWords = $this->keywords;
        $filter = $this->filter;

        $currentUser = $this->getCurrentUser();
        $vipMember = $this->getVipService()->getMemberByUserId($currentUser['id']);
        $currentUserVipLevel = $this->getLevelService()->getLevel($vipMember['levelId']);

        return $this->container->get('twig')->render(
            'VipPlugin:Slot:course-search-btn-slot.html.twig',
            array(
                'filter' => $filter,
                'keywords' => $keyWords,
                'currentUserVipLevel' => $currentUserVipLevel,
            )
        );
    }

    protected function getCurrentUser()
    {
        $biz = $this->container->get('biz');

        return $biz['user'];
    }

    /**
     * @return VipService
     */
    protected function getVipService()
    {
        return $this->container->get('biz')->service('VipPlugin:Vip:VipService');
    }

    /**
     * @return LevelService
     */
    protected function getLevelService()
    {
        return $this->container->get('biz')->service('VipPlugin:Vip:LevelService');
    }

}