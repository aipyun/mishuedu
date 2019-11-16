<?php

namespace VipPlugin\Biz\Vip\Service;

interface VipFacadeService
{
    /**
     * @param $courseId
     *
     * @return array return (success: bool, message: string)
     */
    public function joinCourse($courseId);

    /**
     * @param $classroomId
     *
     * @return array return (success: bool, message: string)
     */
    public function joinClassroom($classroomId);
}
