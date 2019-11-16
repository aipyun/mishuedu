<?php

namespace VipPlugin\Biz\Vip\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface LevelDao extends GeneralDaoInterface
{
    public function findAllLevelsLessThanSeq($seq);

    public function getLevelByName($name);

    public function getVipLevel();

    public function getVipLevelCount();

    public function findLevelsBySeq($seq, $start, $limit);

    public function findLevelsWithEnabled($enabled, $start, $limit);
}
