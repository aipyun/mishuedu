<?php

namespace VipPlugin\Biz\Vip\Dao\Impl;

use Codeages\Biz\Framework\Dao\GeneralDaoImpl;
use VipPlugin\Biz\Vip\Dao\VipHistoryDao;

class VipHistoryDaoImpl extends GeneralDaoImpl implements VipHistoryDao
{
    protected $table = 'vip_history';

    public function declares()
    {
        return array(
            'serializes' => array(),
            'orderbys'   => array('createdTime', 'boughtTime'),
            'timestamps' => array('createdTime'),
            'conditions' => array(
                'levelId = :level',
                'userId = :userId',
                'userId IN ( :userIds)',
                'boughtType = :boughtType',
                'boughtTime <= :boughtTime_LTE',
                'boughtTime > :boughtTime_GT',
                'deadline <= :deadline_LTE',
                'deadline > :deadline_LT',
            )
        );
    }
}
