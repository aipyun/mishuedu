<?php

namespace VipPlugin\Biz\Vip\Event;

use Codeages\PluginBundle\Event\EventSubscriber;
use Codeages\Biz\Framework\Event\Event;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use VipPlugin\Biz\Vip\Service\VipFacadeService;

class CourseEventSubscriber extends EventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array(
            'course.marketing.update'        => 'updateCourseVipStatus',
            'course.try_free_join' => 'tryFreeJoinCourseByVip',
            'classroom.try_free_join' => 'tryFreeJoinClassroomByVip',
        );
    }

    public function tryFreeJoinCourseByVip(Event $event)
    {
        $course = $event->getSubject();
        $this->getVipFacadeService()->joinCourse($course['id']);
    }

    public function tryFreeJoinClassroomByVip(Event $event)
    {
        $classroom = $event->getSubject();
        $this->getVipFacadeService()->joinClassroom($classroom['id']);
    }

    public function updateCourseVipStatus(Event $event)
    {
        $subject = $event->getSubject();
        $oldCourse = $subject['oldCourse'];
        $newCourse = $subject['newCourse'];

        if ($oldCourse['vipLevelId'] != $newCourse['vipLevelId']) {
            $courses = $this->getCourseService()->findCoursesByCourseSetId($newCourse['courseSetId']);
            $isVipCourseSet = 0;
            foreach ($courses as $course) {
                if ($course['vipLevelId'] > 0) {
                    $isVipCourseSet = 1;
                    break;
                }
            }
            $this->getCourseSetDao()->update($newCourse['courseSetId'], array('isVip' => $isVipCourseSet));
        }
    }

    /**
     * @return VipFacadeService
     */
    protected function getVipFacadeService()
    {
        return $this->getBiz()->service('VipPlugin:Vip:VipFacadeService');
    }

    protected function getCourseService()
    {
        return $this->getBiz()->service('Course:CourseService');
    }

    protected function getCourseSetDao()
    {
        return $this->getBiz()->dao('Course:CourseSetDao');
    }
}
