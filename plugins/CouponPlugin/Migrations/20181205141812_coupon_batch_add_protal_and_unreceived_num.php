<?php

use Phpmig\Migration\Migration;

class CouponBatchAddProtalAndUnreceivedNum extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $db = $this->getContainer()->offsetGet('db');
        $db->exec("ALTER TABLE `coupon_batch` ADD `h5MpsEnable` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '通过商品详情页小程序/微网校渠道发放' AFTER `fullDiscountPrice`;");
        $db->exec("ALTER TABLE `coupon_batch` ADD `linkEnable` TINYINT(1) UNSIGNED NOT NULL DEFAULT '1' COMMENT '通过链接渠道发放' AFTER `fullDiscountPrice`;");
        $db->exec("ALTER TABLE `coupon_batch` ADD `codeEnable` TINYINT(1) UNSIGNED NOT NULL DEFAULT '1' COMMENT '通过优惠码渠道发放' AFTER `fullDiscountPrice`;");
        $db->exec("ALTER TABLE `coupon_batch` ADD `unreceivedNum` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '未领取的数量' AFTER `fullDiscountPrice`;");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $db = $this->getContainer()->offsetGet('db');
        $db->exec("ALTER TABLE `coupon_batch` DROP COLUMN `h5MpsEnable`;");
        $db->exec("ALTER TABLE `coupon_batch` DROP COLUMN `linkEnable`;");
        $db->exec("ALTER TABLE `coupon_batch` DROP COLUMN `codeEnable`;");
        $db->exec("ALTER TABLE `coupon_batch` DROP COLUMN `unreceivedNum`;");
    }
}
