<?php

namespace VipPlugin\Biz\Vip\Service;

interface LevelService
{
    public function getLevel($levelId);

    public function getLevelByName($name);

    public function getVipLevel();

    public function getVipLevelCount();

    public function createLevel($level);

    public function findLevelsBySeq($seq, $start, $limit);

    public function findAllLevelsLessThanSeq($seq);

    public function findEnabledLevels();

    public function findNextEnabledLevels($levelId);

    public function findPrevEnabledLevels($levelId);

    public function searchLevelsCount($conditions);

    public function searchLevels($conditions, $orderbys, $start, $limit);

    public function deleteLevel($levelId);

    public function updateLevel($levelId, $fields);

    public function sortLevels(array $levelIds);

    public function onLevel($levelId);

    public function offLevel($levelId);
}
