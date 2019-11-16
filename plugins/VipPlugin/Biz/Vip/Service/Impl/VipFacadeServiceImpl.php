<?php

namespace VipPlugin\Biz\Vip\Service\Impl;


use Biz\BaseService;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\CourseSetService;
use Biz\Course\Service\MemberService;
use Biz\Order\Service\OrderService;
use VipPlugin\Biz\Vip\Service\LevelService;
use VipPlugin\Biz\Vip\Service\VipFacadeService;
use VipPlugin\Biz\Vip\Service\VipService;

class VipFacadeServiceImpl extends BaseService implements VipFacadeService
{
    public function joinCourse($courseId)
    {
        $course      = $this->getCourseService()->getCourse($courseId);

        if(!empty($course['buyExpiryTime']) && time() > $course['buyExpiryTime']){ //课程已经过了购买截止期
            return array(false, '当前教学计划购买时间已截止');
        }

        $currentUser = $this->getCurrentUser();

        if ($this->unableJoin($course, $currentUser)) {
            return array(false, '不满足VIP加入课程的条件');
        }

        //校验VIP
        $status = $this->getVipService()->checkUserInMemberLevel($currentUser['id'], $course['vipLevelId']);
        if ($status == 'ok') {
            $this->beginTransaction();
            //加入课程
            try {
                $this->becomeCourseStudent($course, $currentUser);
                $this->commit();

                return array(true, '');
            } catch (\Exception $e) {
                $this->rollback();
                return array(false, '加入VIP课程失败! ');
            }
        }

        return array(false, '不满足VIP加入课程的条件');
    }

    public function joinClassroom($classroomId)
    {
        $classroom = $this->getClassroomService()->getClassroom($classroomId);

        $currentUser = $this->getCurrentUser();

        if ($this->unableJoin($classroom, $currentUser)) {
            return array(false, '不满足VIP加入班级的条件');
        }

        //校验VIP
        $status = $this->getVipService()->checkUserInMemberLevel($currentUser['id'], $classroom['vipLevelId']);
        if ($status == 'ok') {
            $this->beginTransaction();
            //加入班级
            try {
                $this->becomeClassroomStudent($classroom, $currentUser);
                $this->commit();

                return array(true, '');
            } catch (\Exception $e) {
                $this->rollback();
                return array(false, '加入VIP班级失败! ');
            }
        }
        return array(false, '不满足VIP加入班级的条件');
    }

    private function becomeCourseStudent($course, $currentUser)
    {
        $vip = $this->getVipService()->getMemberByUserId($currentUser['id']);

        $info = array(
            'orderId' => 0,
            'levelId' => $vip['levelId'],
            'reason' => 'site.join_by_vip',
            'reason_type' => 'vip_join',
        );

        $this->getCourseMemberService()->becomeStudent(
            $course['id'],
            $currentUser['id'],
            $info
        );
    }

    private function becomeClassroomStudent($classroom, $currentUser)
    {
        $info = array(
            'orderId' => 0,
            'becomeUseMember' => true, //vip 加入
            'reason' => 'site.join_by_vip',
            'reason_type' => 'vip_join',
        );

        $this->getClassroomService()->becomeStudent(
            $classroom['id'],
            $currentUser['id'],
            $info
        );
    }

    private function unableJoin($buyStaff, $currentUser)
    {
        return !$buyStaff
            || $buyStaff['vipLevelId'] == 0
            || !$currentUser->isLogin();
    }

    /**
     * @return ClassroomService
     */
    protected function getClassroomService()
    {
        return $this->createService('Classroom:ClassroomService');
    }

    /**
     * @return OrderService
     */
    protected function getOrderService()
    {
        return $this->createService('Order:OrderService');
    }

    /**
     * @return MemberService
     */
    protected function getCourseMemberService()
    {
        return $this->createService('Course:MemberService');
    }

    /**
     * @return VipService
     */
    protected function getVipService()
    {
        return $this->createService('VipPlugin:Vip:VipService');
    }

    /**
     * @return CourseSetService
     */
    protected function getCourseSetService()
    {
        return $this->createService('Course:CourseSetService');
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    /**
     * @return LevelService
     */
    protected function getVipLevelService()
    {
        return $this->createService('VipPlugin:Vip:LevelService');
    }
}
