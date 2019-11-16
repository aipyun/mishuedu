<?php


namespace VipPlugin\Slot;


use Codeages\PluginBundle\System\Slot\SlotInjection;

class CourseMarketingExtensionSlot extends SlotInjection
{
    public function inject()
    {
        return $this->container->get('twig')->render(
            'VipPlugin:Slot:course-vip-marketing-tag-slot.html.twig',
            array('course' => $this->course)
        );
    }
}