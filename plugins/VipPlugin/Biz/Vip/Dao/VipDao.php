<?php

namespace VipPlugin\Biz\Vip\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface VipDao extends GeneralDaoInterface
{
    public function getByUserId($userId);

    public function deleteByUserId($userId);
}
