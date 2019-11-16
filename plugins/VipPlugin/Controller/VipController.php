<?php

namespace VipPlugin\Controller;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\Paginator;
use AppBundle\Controller\BaseController;
use Biz\Course\Service\CourseService;
use Symfony\Component\HttpFoundation\Request;

class VipController extends BaseController
{
    public function indexAction(Request $request)
    {
        if (!$this->setting('vip.enabled')) {
            return $this->createMessageResponse('info', '会员专区已关闭');
        }

        $levels = $this->getLevelService()->searchLevels(array('enabled' => 1), array('seq' => 'asc'), 0, 100);

        $levels = ArrayToolkit::index($levels, 'id');
        foreach ($levels as $key => $value) {
            $currentLevelPreLevelIds = $this->getPrevLevelIds($value['id']); //
            $conditions = array(
                'status' => 'published',
                'parentId' => 0,
                'vipLevelIds' => $currentLevelPreLevelIds,
            );
            $conditions = $this->getCourseService()->appendReservationConditions($conditions);

            $currentLevelCourses = $this->getCourseService()->searchCourses(
                $conditions,
                array('createdTime' => 'DESC'),
                0,
                PHP_INT_MAX
            );
            $courseSetIds = ArrayToolkit::column($currentLevelCourses, 'courseSetId');
            $currentLevelClassroomCount = $this->getClassroomService()->countClassrooms(
                array(
                    'status' => 'published',
                    'vipLevelIds' => $currentLevelPreLevelIds,
                )
            );
            $levels[$key]['courseSetCount'] = count(array_unique($courseSetIds));
            $levels[$key]['classroomCount'] = $currentLevelClassroomCount;
        }

        $currentUser = $this->getCurrentUser();
        $userVip = $currentUser->isLogin() ? $this->getVipService()->getMemberByUserId($currentUser['id']) : null;

        return $this->render(
            'VipPlugin:Vip:index.html.twig',
            array(
                'userVip' => $userVip,
                'levels' => $levels,
            )
        );
    }

    public function vipBannerAction(Request $request, $userVip)
    {
        $deadlineAlertCookie = $request->cookies->get('deadlineAlert');
        $level = $this->getLevelService()->getLevel($userVip['levelId']);

        if (!empty($userVip)) {
            $nextlevel = $this->getLevelService()->findNextEnabledLevels($userVip['levelId']);
        }

        return $this->render(
            'VipPlugin:Vip:vip-banner.html.twig',
            array(
                'userVip' => $userVip,
                'nowTime' => time(),
                'nextlevel' => isset($nextlevel) ? $nextlevel : array(),
                'level' => $level,
                'deadlineAlertCookie' => $deadlineAlertCookie,
            )
        );
    }

    public function vipIntroduceAction(Request $request, $userVip, $levels)
    {
        $coinSetting = $this->getSettingService()->get('coin', array());
        $cashRate = isset($coinSetting['cash_rate']) ? $coinSetting['cash_rate'] : 1;

        return $this->render(
            'VipPlugin:Vip:vip-introduce.html.twig',
            array(
                'levels' => $levels,
                'levelsCount' => count($levels),
                'buyType' => $this->setting('vip.buyType'),
                'cashRate' => $cashRate,
                'userVip' => $userVip,
            )
        );
    }

    public function vipPrivilegeAction(Request $request, $levels)
    {
        $levelId = 0;
        $firstLevel = $this->getLevelService()->searchLevels(array('enabled' => 1), array('seq' => 'asc'), 0, 1);

        if (!empty($firstLevel)) {
            $levelId = $firstLevel[0]['id'];
        }

        return $this->render(
            'VipPlugin:Vip:vip-privilege.html.twig',
            array(
                'levels' => $levels,
                'levelId' => $levelId,
            )
        );
    }

    public function vipNewestMemberAction(Request $request)
    {
        $users = array();
        $members = $this->getVipService()->searchMembers(array(), array('createdTime' => 'DESC'), 0, 20);

        if ($members) {
            $memberIds = ArrayToolkit::column($members, 'userId');
            $users = $this->getUserService()->findUsersByIds($memberIds);
        }

        return $this->render(
            'VipPlugin:Vip:vip-newestmember.html.twig',
            array(
                'members' => $members,
                'users' => $users,
            )
        );
    }

    private function getPrevLevelIds($levelId)
    {
        $vipLevelIds = ArrayToolkit::column($this->getLevelService()->findPrevEnabledLevels($levelId), 'id');

        if (empty($levelId)) {
            return $vipLevelIds;
        }

        return array_merge(array($levelId), $vipLevelIds);
    }

    public function vipCoursesAction(Request $request, $levelId)
    {
        $preLevelIds = $this->getPrevLevelIds($levelId);

        $courses = array();

        if (!empty($preLevelIds)) {
            $conditions = array(
                'status' => 'published',
                'parentId' => 0,
                'vipLevelIds' => $preLevelIds,
            );

            $conditions = $this->getCourseService()->appendReservationConditions($conditions);

            $courses = $this->getCourseService()->searchCourses(
                $conditions,
                'latest',
                0,
                20
            );
        }

        $courseSetIds = array();
        foreach ($courses as $key => $value) {
            $courseSetIds[] = $value['courseSetId'];
        }

        $courseSetIds = array_unique($courseSetIds);
        $courseSets = array();
        if (!empty($courseSetIds)) {
            $courseSetConditions = array('ids' => $courseSetIds);
            $courseSetConditions = $this->getCourseService()->appendReservationConditions($courseSetConditions);
            $courseSets = $this->getCourseSetService()->searchCourseSets(
                $courseSetConditions,
                'latest',
                0,
                4
            );
        }
        if (!empty($courseSets)) {
            foreach ($courseSets as &$courseSet) {
                foreach ($courses as $course) {
                    if ($course['courseSetId'] == $courseSet['id']) {
                        $courseSet['course'] = $course;
                        break;
                    }
                }
            }
        }

        return $this->render(
            'VipPlugin:Vip:vipCourses.html.twig',
            array('levelId' => $levelId, 'courseSets' => $courseSets)
        );
    }

    public function vipClassroomsAction(Request $request, $levelId)
    {
        $preLevelIds = $this->getPrevLevelIds($levelId);

        $classrooms = array();

        if (!empty($preLevelIds)) {
            $conditions = array(
                'status' => 'published',
                'vipLevelIds' => $preLevelIds,
                'showable' => '1',
            );

            $classrooms = $this->getClassroomService()->searchClassrooms(
                $conditions,
                array('createdTime' => 'desc'),
                0,
                4
            );
        }

        return $this->render(
            'VipPlugin:Vip:vipClassrooms.html.twig',
            array('levelId' => $levelId, 'classrooms' => $classrooms)
        );
    }

    public function courseAction(Request $request, $levelId)
    {
        if (!$this->setting('vip.enabled')) {
            return $this->createMessageResponse('info', '会员专区已关闭');
        }

        if (!empty($levelId)) {
            $level = $this->getLevelService()->getLevel($levelId);

            if (empty($level)) {
                throw $this->createNotFoundException();
            }
        } else {
            $level = array('id' => null);
        }

        $conditions = array(
            'status' => 'published',
            'parentId' => 0,
        );

        if (!empty($level['id'])) {
            $vipLevelIds = ArrayToolkit::column($this->getLevelService()->findPrevEnabledLevels($level['id']), 'id');
            $conditions['vipLevelIds'] = array_merge(array($level['id']), $vipLevelIds);
        } else {
            $conditions['vipLevelIdGreaterThan'] = 1;
        }

        $sort = $request->query->get('sort', 'latest');

        $paginator = new Paginator(
            $this->get('request'),
            $this->getCourseService()->searchCourseCount($conditions),
            9
        );

        $courses = $this->getCourseService()->searchCourses(
            $conditions,
            $sort,
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $levels = $this->getLevelService()->findEnabledLevels();

        return $this->render(
            'VipPlugin:Vip:course.html.twig',
            array(
                'levels' => $levels,
                'courses' => $courses,
                'paginator' => $paginator,
                'level' => $level,
                'sort' => $sort,
            )
        );
    }

    public function historyAction(Request $request)
    {
        if (!$this->setting('vip.enabled')) {
            return $this->createMessageResponse('info', '会员专区已关闭');
        }

        $deadlineAlertCookie = $request->cookies->get('deadlineAlert');

        $conditions = array();

        $currentUser = $this->getCurrentUser();
        $members = $this->getVipService()->searchMembers($conditions, array('createdTime' => 'DESC'), 0, 10);
        $memberIds = ArrayToolkit::column($members, 'userId');
        $latestMembers = $this->getUserService()->findUsersByIds($memberIds);
        $levels = $this->getLevelService()->searchLevels(array('enabled' => 1), array(), 0, 100);
        $member = $this->getVipService()->getMemberByUserId($currentUser['id']);

        $conditions = array('nickname' => $currentUser['nickname']);

        $paginator = new Paginator(
            $this->get('request'),
            $this->getVipService()->searchMembersHistoriesCount($conditions),
            20
        );

        $memberHistories = $this->getVipService()->searchMembersHistories(
            $conditions,
            array('boughtTime' => 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        return $this->render(
            'VipPlugin:Vip:history.html.twig',
            array(
                'levels' => $levels,
                'latestMembers' => $latestMembers,
                'members' => $members,
                'userVip' => $member,
                'nowTime' => time(),
                'memberHistories' => $memberHistories,
                'paginator' => $paginator,
                'deadlineAlertCookie' => $deadlineAlertCookie,
            )
        );
    }

    public function orderInfoAction(Request $request, $sn)
    {
        $order = $this->getOrderService()->getOrderBySn($sn);

        if (empty($order)) {
            throw $this->createNotFoundException('订单不存在!');
        }

        $level = $this->getLevelService()->getLevel($order['targetId']);

        if (empty($level)) {
            throw $this->createNotFoundException('找不到会员等级!');
        }

        return $this->render('VipPlugin:Vip:vip-order.html.twig', array('order' => $order, 'level' => $level));
    }

    protected function getUserService()
    {
        return $this->createService('User:UserService');
    }

    protected function getLevelService()
    {
        return $this->createService('VipPlugin:Vip:LevelService');
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    protected function getCourseSetService()
    {
        return $this->createService('Course:CourseSetService');
    }

    protected function getVipService()
    {
        return $this->createService('VipPlugin:Vip:VipService');
    }

    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }

    protected function getClassroomService()
    {
        return $this->createService('Classroom:ClassroomService');
    }

    protected function getOrderService()
    {
        return $this->createService('Order:OrderService');
    }
}
