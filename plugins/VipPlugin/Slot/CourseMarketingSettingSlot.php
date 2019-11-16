<?php

namespace VipPlugin\Slot;

use Codeages\PluginBundle\System\Slot\SlotInjection;
use AppBundle\Common\ArrayToolkit;

class CourseMarketingSettingSlot extends SlotInjection
{
    public function inject()
    {
        $levels = $this->getVipLevelService()->findEnabledLevels();
        return $this->container->get('twig')->render(
            'VipPlugin:Slot:course-vip-setting-slot.html.twig',
            array('levels' => array_column($levels, 'name', 'id'), 'course' => $this->course)
        );
    }

    protected function getVipLevelService()
    {
        return $this->container->get('biz')->service('VipPlugin:Vip:LevelService');
    }
}
