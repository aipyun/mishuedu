<?php

namespace VipPlugin\Biz\Vip\Dao\Impl;

use VipPlugin\Biz\Vip\Dao\VipDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class VipDaoImpl extends GeneralDaoImpl implements VipDao
{
    protected $table = 'vip';

    public function declares()
    {
        return array(
            'serializes' => array(),
            'orderbys'   => array('createdTime', 'deadline', 'userId', 'boughtTime'),
            'timestamps' => array('createdTime'),
            'conditions' => array(
                'levelId = :level',
                'userId = :userId',
                'userId IN ( :userIds)',
                'boughtType = :boughtType',
                'boughtTime <= :boughtTime_LTE',
                'boughtTime >= :boughtTime_GTE',
                'boughtTime > :boughtTime_GT',
                'deadline <= :deadline_LTE',
                'deadline < :deadline_LT',
                'deadline >= :deadline_GTE',
            )
        );
    }

    public function deleteByUserId($userId)
    {
        return $this->db()->delete($this->table(), array('userId' => $userId));
    }

    public function getByUserId($userId)
    {
        return $this->getByFields(array('userId' => $userId));
    }
}
