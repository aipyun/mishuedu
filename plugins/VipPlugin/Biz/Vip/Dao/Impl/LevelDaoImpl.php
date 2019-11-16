<?php

namespace VipPlugin\Biz\Vip\Dao\Impl;

use VipPlugin\Biz\Vip\Dao\LevelDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class LevelDaoImpl extends GeneralDaoImpl implements LevelDao
{
    protected $table = 'vip_level';

    public function declares()
    {
        return array(
            'serializes' => array(),
            'orderbys'   => array('seq'),
            'timestamps' => array('createdTime'),
            'conditions' => array(
                'name LIKE :name',
                'icon = :icon',
                'enabled = :enabled',
                'seq < :seq',
                'id IN ( :ids )',
            )
        );
    }

    public function findAllLevelsLessThanSeq($seq)
    {
        $sql = "SELECT * FROM {$this->table} WHERE seq <= ? AND enabled = '1'  ";

        return $this->db()->fetchAll($sql, array($seq)) ?: array();
    }

    public function getLevelByName($name)
    {
        $sql = "SELECT * FROM {$this->table} WHERE name = ? LIMIT 1";

        return $this->db()->fetchAssoc($sql, array($name)) ?: null;
    }

    public function getVipLevel()
    {
        $sql = "SELECT seq,name FROM {$this->table} WHERE enabled = '1' order by seq";

        return $this->db()->fetchAll($sql) ?: null;
    }

    public function getVipLevelCount()
    {
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE enabled = '1' ";

        return $this->db()->fetchColumn($sql);
    }

    public function findLevelsBySeq($seq, $start, $limit)
    {
        $sql = "SELECT * FROM {$this->table} WHERE seq >= ? AND enabled = '1' LIMIT {$start}, {$limit}";

        return $this->db()->fetchAll($sql, array($seq)) ?: array();
    }

    public function findLevelsWithEnabled($enabled, $start, $limit)
    {
        $sql = "SELECT * FROM {$this->table} WHERE enabled = ? ORDER BY seq ASC";
        $sql = $this->sql($sql, array(), $start, $limit);

        return $this->db()->fetchAll($sql, array($enabled)) ?: array();
    }
}
