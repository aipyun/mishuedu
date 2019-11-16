<?php

namespace VipPlugin\Biz;

use Biz\LoggerConstantInterface;

class LoggerConstant implements LoggerConstantInterface
{
    /**
     * [$vip 会员].
     *
     * @var string
     */
    const VIP = 'vip';

    public function getActions()
    {
        return array(
            self::VIP => array(
                'create_level',
                'update_level',
                'on_level',
                'off_level',
                'delete_level',
                'create_member',
                'renew_member',
                'upgrade_member',
                'update_member',
                'delete_member',

            ),
        );
    }

    public function getModules()
    {
        return array(
            self::VIP,
        );
    }
}