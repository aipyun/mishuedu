<?php

use Phpmig\Migration\Migration;

class CourseSetAddIsVip extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $db = $this->getContainer()->offsetGet('db');
        $db->exec("ALTER TABLE `course_set_v8` ADD `isVip` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '是否是VIP课程' AFTER `locked`;");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $db = $this->getContainer()->offsetGet('db');
        $db->exec("ALTER TABLE `course_set_v8` DROP COLUMN `isVip`;");
    }
}
