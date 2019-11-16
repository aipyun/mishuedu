<?php

namespace VipPlugin\Biz\Vip\Service\Impl;

use VipPlugin\Biz\Vip\Service\LevelService;
use Biz\BaseService;

class LevelServiceImpl extends BaseService implements LevelService
{
    const MAX_LEVEL = 100;

    public function getLevel($levelId)
    {
        return $this->getLevelDao()->get($levelId);
    }

    public function findAllLevelsLessThanSeq($seq)
    {
        return $this->getLevelDao()->findAllLevelsLessThanSeq($seq);
    }

    public function getLevelByName($name)
    {
        return $this->getLevelDao()->getLevelByName($name);
    }

    public function getVipLevel()
    {
        return $this->getLevelDao()->getVipLevel();
    }

    public function getVipLevelCount()
    {
        return $this->getLevelDao()->getVipLevelCount();
    }

    public function findLevelsBySeq($seq, $start, $limit)
    {
        return $this->getLevelDao()->findLevelsBySeq($seq, $start, $limit);
    }

    public function findEnabledLevels()
    {
        return $this->getLevelDao()->findLevelsWithEnabled(1, 0, self::MAX_LEVEL);
    }

    public function findNextEnabledLevels($levelId)
    {
        $this->getLevel($levelId);
        $levels = $this->findEnabledLevels();
        $nextLevels = array();
        $ok = false;

        foreach ($levels as $level) {
            if ($levelId == $level['id']) {
                $ok = true;
                continue;
            }

            if ($ok) {
                $nextLevels[] = $level;
            }
        }

        return $nextLevels;
    }

    public function findPrevEnabledLevels($levelId)
    {
        $this->getLevel($levelId);
        $levels = $this->findEnabledLevels();
        $prevLevels = array();

        foreach ($levels as $level) {
            if ($levelId == $level['id']) {
                break;
            }

            $prevLevels[] = $level;
        }

        return $prevLevels;
    }

    public function searchLevelsCount($conditions)
    {
        return $this->getLevelDao()->count($conditions);
    }

    public function generateNextSeq()
    {
        return $this->searchLevelsCount(array()) + 1;
    }

    public function createLevel($level)
    {
        $level['createdTime'] = time();
        @$level['seq'] = $this->generateNextSeq();
        $level = $this->getLevelDao()->create($level);
        $this->getLogService()->info('vip', 'create_level', "添加会员等级{$level['name']}(#{$level['id']})", $level);

        return $level;
    }

    public function searchLevels($conditions, $orderbys, $start, $limit)
    {
        return $this->getLevelDao()->search($conditions, $orderbys, $start, $limit);
    }

    public function updateLevel($levelId, $fields)
    {
        $oldLevel = $this->getLevel($levelId);

        if (!empty($oldLevel)) {
            $level = $this->getLevelDao()->update($levelId, $fields);
            $this->getLogService()->info('vip', 'update_level', "编辑会员等级{$level['name']}(#{$level['id']})", $level);
            if ($oldLevel['name'] != $level['name']) {
                $this->dispatchEvent(
                    'vip.level.name.update',
                    $level
                );
            }

            return $level;
        }
    }

    public function sortLevels(array $levelIds)
    {
        $seq = 0;

        foreach ($levelIds as $itemId) {
            ++$seq;
            $item = $this->getLevel($itemId);
            $fields = array('seq' => $seq);

            if ($fields['seq'] != $item['seq']) {
                $this->updateLevel($item['id'], $fields);
            }
        }
    }

    public function onLevel($levelId)
    {
        $level = $this->getLevel($levelId);

        if (empty($level)) {
            throw $this->createServiceException('会员等级不存在，开启失败！');
        }

        $this->getLevelDao()->update($level['id'], array('enabled' => 1));

        $this->getLogService()->info('vip', 'on_level', "会员等级{$level['name']}(#{$level['id']})允许加入会员", $level);

        return true;
    }

    public function offLevel($levelId)
    {
        $level = $this->getLevel($levelId);

        if (empty($level)) {
            throw $this->createServiceException('会员等级不存在，关闭失败！');
        }

        $this->getLevelDao()->update($level['id'], array('enabled' => 0));

        $this->getLogService()->info('vip', 'off_level', "会员等级{$level['name']}(#{$level['id']})禁止加入会员", $level);

        return true;
    }

    public function deleteLevel($levelId)
    {
        $level = $this->getLevel($levelId);
        $this->getLogService()->info('vip', 'delete_level', "删除用户类型{$level['name']}(#{$level['id']})", $level);
        $affect = $this->getLevelDao()->delete($levelId);
        $this->dispatchEvent(
            'vip.level.delete',
            $level
        );

        return $affect;
    }

    private function getLevelDao()
    {
        return $this->createDao('VipPlugin:Vip:LevelDao');
    }

    protected function getLogService()
    {
        return $this->createService('System:LogService');
    }
}
