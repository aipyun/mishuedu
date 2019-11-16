<?php

namespace VipPlugin\Slot;

use Codeages\PluginBundle\System\Slot\SlotInjection;

class MyAccountMenuSlot extends SlotInjection
{
    public function inject()
    {
        $sideNav = $this->sideNav;

        return $this->container->get('twig')->render('VipPlugin:Slot:my-account-menu.html.twig', array(
            'side_nav' => $sideNav,
        ));
    }
}
