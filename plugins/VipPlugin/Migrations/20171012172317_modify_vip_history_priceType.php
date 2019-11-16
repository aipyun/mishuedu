<?php

use Phpmig\Migration\Migration;

class ModifyVipHistoryPriceType extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $biz['db']->exec("alter table `vip_history` modify column `priceType` varchar(255) NOT NULL DEFAULT '' COMMENT '价格类型';");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $biz['db']->exec("alter vip_history modify priceType ENUM('RMB','Coin') NOT NULL DEFAULT 'RMB'");
    }
}
