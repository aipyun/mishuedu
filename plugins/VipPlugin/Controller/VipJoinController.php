<?php

namespace VipPlugin\Controller;

use AppBundle\Controller\BaseController;
use Biz\Course\Service\CourseService;

class VipJoinController extends BaseController
{
    public function joinCourseAction($courseId)
    {
        list($success, $message) = $this->getFacadeService()->joinCourse($courseId);

        if(!$success){
            throw $this->createAccessDeniedException($message);
        }

        return $this->redirect($this->generateUrl('my_course_show', array('id' => $courseId)));
    }

    public function joinClassroomAction($classroomId)
    {
        list($success, $message) = $this->getFacadeService()->joinClassroom($classroomId);

        if(!$success){
            throw $this->createAccessDeniedException($message);
        }

        return $this->redirect($this->generateUrl('classroom_courses', array('classroomId' => $classroomId)));
    }

    public function joinNeedApproveAction($courseId)
    {
        $course = $this->getCourseService()->getCourse($courseId);

        return $this->render('course/order/approve-modal.html.twig', array(
            'course' => $course,
        ));
    }

    /**
     * @return VipCourseFacadeService
     */
    protected function getFacadeService()
    {
        return $this->createService('VipPlugin:Vip:VipFacadeService');
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }
}
