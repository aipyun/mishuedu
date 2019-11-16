<?php

namespace VipPlugin\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\FileToolkit;
use AppBundle\Common\Paginator;
use AppBundle\Controller\Admin\BaseController;

class VipAdminController extends BaseController
{
    public function indexAction(Request $request, $type)
    {
        $fields = $request->query->all();
        $fields['type'] = $type;

        $conditions = array(
            'nickname' => '',
            'level' => '',
            'deadline' => '',
        );

        if (!empty($fields)) {
            $conditions = $fields;
        }

        if ($type == 'will_expire') {
            $order = array('deadline' => 'ASC');
        } elseif ($type == 'just_expire') {
            $order = array('deadline' => 'DESC');
        } else {
            $order = array('createdTime' => 'DESC');
        }

        $paginator = new Paginator(
            $this->get('request'),
            $memberCount = $this->getVipService()->searchMembersCount($conditions),
            20
        );

        $members = $this->getVipService()->searchMembers(
            $conditions,
            $order,
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($members, 'userId'));

        $levels = $this->makeMemberLevelOptions();
        $levels_enabled = $this->makeMemberLevelOptions('enabled');

        return $this->render('VipPlugin:VipAdmin:index.html.twig', array(
            'members' => $members,
            'paginator' => $paginator,
            'memberCount' => $memberCount,
            'levels' => $levels,
            'levels_enabled' => $levels_enabled,
            'users' => $users,
            'type' => $type,
        ));
    }

    public function createAction(Request $request)
    {
        if ($request->getMethod() == 'POST') {
            $formData = $request->request->all();

            $user = $this->getUserService()->getUserByNickname($formData['nickname']);

            $member = $this->getVipService()->becomeMember(
                $user['id'],
                $formData['levelId'],
                $formData['boughtDuration'],
                $formData['boughtUnit'],
                $orderId = 0
            );

            $level = $this->getLevelService()->getLevel($member['levelId']);

            return $this->render('VipPlugin:VipAdmin:member-table-tr.html.twig', array(
                'member' => $member,
                'user' => $user,
                'type' => 'all',
                'level' => $level['name'],
            ));
        }

        $levels_enabled = $this->makeMemberLevelOptions($operate_type = 'enabled');

        return $this->render('VipPlugin:VipAdmin:modal.html.twig', array(
            'levels_enabled' => $levels_enabled,
        ));
    }

    public function exportAction(Request $request)
    {
        $fields = $request->query->all();

        if (!empty($fields)) {
            $conditions = $fields;
        }

        $paginator = new Paginator(
            $this->get('request'),
            $membersCount = $this->getVipService()->searchMembersCount($conditions),
            PHP_INT_MAX
        );

        $totalPage = $paginator->getLastPage();

        $members = $this->getVipService()->searchMembers($conditions, array('userId' => 'DESC'), 0, $membersCount);

        $exportMembers = array();

        $users = $this->getUserService()->searchUsers(
            array('userIds' => ArrayToolkit::column($members, 'userId')),
            array('id' => 'DESC'),
            0,
            PHP_INT_MAX
        );

        if (!empty($members)) {
            foreach ($users as $key => $user) {
                $userProfile = $this->getUserService()->getUserProfile($user['id']);
                $information = '';
                $information .= $users[$key]['nickname'] . ',';
                $information .= $users[$key]['email'] ? $users[$key]['email'] . ',' : '-' . ',';
                $information .= $users[$key]['verifiedMobile'] ? $users[$key]['verifiedMobile'] . ',' : $userProfile['mobile'] . ',';
                $level = $this->getLevelService()->getLevel($members[$key]['levelId']);
                $information .= $members[$key]['levelId'] ? $level['name'] . ',' : '-' . ',';
                $information .= $members[$key]['deadline'] ? date('Y-m-d', $members[$key]['deadline']) . ',' : '-' . ',';
                $exportMembers[] = $information;
            }
        }

        $str = "用户名,email,手机号码,会员等级,到期日期\r\n";

        for ($i = 1; $i <= $totalPage; ++$i) {
            $str .= implode("\r\n", $exportMembers);
            $str = str_replace('"', '＂', $str);
        }

        $str = chr(239) . chr(187) . chr(191) . $str;
        $filename = sprintf('exportvip-(%s).csv', date('Y-n-d'));

        $response = new Response();
        $response->headers->set('Content-type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $filename . '"');
        $response->headers->set('Content-length', strlen($str));
        $response->setContent($str);

        $this->getLogService()->info('vip', 'exportCsv', "导出会员资料 {$membersCount} 条");

        return $response;
    }

    public function nicknameCheckAction(Request $request)
    {
        $nickname = $request->query->get('value');
        list($result, $message) = $this->getVipService()->checkMemberName($nickname);

        if ($result == 'success') {
            $response = array('success' => true, 'message' => 'json_response.nickname_can_use.message');
        } else {
            $response = array('success' => false, 'message' => $message);
        }

        return $this->createJsonResponse($response);
    }

    public function boughtHistoryAction(Request $request)
    {
        $userId = $request->query->get('userId');

        if (empty($userId)) {
            throw $this->createNotFoundException();
        }

        $user = $this->getUserService()->getUser($userId);

        if (empty($user)) {
            throw $this->createNotFoundException();
        }

        $conditions = array('userId' => $userId);

        $paginator = new Paginator(
            $this->get('request'),
            $memberHistoriesCount = $this->getVipService()->searchMembersHistoriesCount($conditions),
            20
        );

        $memberHistories = $this->getVipService()->searchMembersHistories(
            $conditions,
            array('boughtTime' => 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $levels = $this->makeMemberLevelOptions();
        $operators = $this->getUserService()->findUsersByIds(ArrayToolkit::column($memberHistories, 'operatorId'));

        return $this->render('VipPlugin:VipAdmin:bought-history.html.twig', array(
            'memberHistories' => $memberHistories,
            'paginator' => $paginator,
            'user' => $user,
            'levels' => $levels,
            'operators' => $operators,
        ));
    }

    public function boughtListAction(Request $request)
    {
        $fields = $request->query->all();

        $conditions = $fields;

        if (isset($fields['endDateTime']) && !empty($fields['endDateTime'])) {
            $conditions['boughtTime_LTE'] = strtotime($fields['endDateTime']);
        }

        if (isset($fields['startDateTime']) && !empty($fields['startDateTime'])) {
            $conditions['boughtTime_GT'] = strtotime($fields['startDateTime']);
        }
        unset($conditions['endDateTime']);
        unset($conditions['startDateTime']);

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

        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($memberHistories, 'userId'));
        $operators = $this->getUserService()->findUsersByIds(ArrayToolkit::column($memberHistories, 'operatorId'));

        $levels = $this->makeMemberLevelOptions();

        return $this->render('VipPlugin:VipAdmin:bought-list.html.twig', array(
            'memberHistories' => $memberHistories,
            'paginator' => $paginator,
            'menu' => 'member_history',
            'show_usernick' => 1,
            'levels' => $levels,
            'users' => $users,
            'operators' => $operators,
        ));
    }

    public function editAction(Request $request)
    {
        $userIds = $request->request->get('userIds', array());
        $userIds = is_array($userIds) ? $userIds : array($userIds);

        $members = $this->getVipService()->searchMembers(
            array('userIds' => $userIds),
            array('createdTime' => 'DESC'),
            0,
            PHP_INT_MAX
        );
        $users = $this->getUserService()->findUsersByIds($userIds);
        $levels_enabled = $this->makeMemberLevelOptions('enabled');

        return $this->render('VipPlugin:VipAdmin:modal.html.twig', array(
            'members' => $members,
            'users' => $users,
            'levels_enabled' => $levels_enabled,
        ));
    }

    public function editSaveAction(Request $request)
    {
        if ($request->getMethod() == 'POST') {
            $formData = $request->request->all();

            $level = $this->getLevelService()->getLevel($formData['levelId']);

            $this->getVipService()->updateMembers($formData['userIds'], $formData);

            return $this->createJsonResponse(true);
        }

        return $this->createJsonResponse(false);
    }

    public function cancelAction(Request $request)
    {
        if ($request->getMethod() == 'POST') {
            $userIds = $request->request->get('userIds', array());

            if (empty($userIds)) {
                return $this->createJsonResponse(false);
            }

            $this->getVipService()->cancelMembers($userIds);

            return $this->createJsonResponse(true);
        }
    }

    public function orderAction(Request $request)
    {
        return $this->forward('AppBundle:Admin/Order:manage', array(
            'request' => $request,
            'targetType' => 'vip',
            'layout' => 'VipPlugin:VipAdmin:order.layout.html.twig',
        ));
    }

    public function settingAction(Request $request)
    {
        $vipSetting = $this->getSettingService()->get('vip', array());

        $default = array(
            'buyType' => 30,
            'enabled' => 1,
            'default_buy_months' => 3,
            'upgrade_min_day' => 30,
            'deadlineNotify' => 0,
            'daysOfNotifyBeforeDeadline' => 0,
            'poster' => '',
            'bgcolor' => '#f13b54',
        );

        $vipSetting = array_merge($default, $vipSetting);

        if ($request->getMethod() == 'POST') {
            $vipSetting = $request->request->all();

            if ($vipSetting['buyType'] == '10') {
                $vipSetting['default_buy_months'] = $vipSetting['default_buy_months10'];
                $vipSetting['default_buy_years'] = $vipSetting['default_buy_years10'];
                //按年
            } elseif ($vipSetting['buyType'] == '20') {
                $vipSetting['default_buy_years10'] = $vipSetting['default_buy_years'];
                //按月
            } else {
                $vipSetting['default_buy_months10'] = $vipSetting['default_buy_months'];
            }

            $this->getSettingService()->set('vip', $vipSetting);
            $this->getLogService()->info('vip', 'update_setting', '更新会员专区设置', $vipSetting);
            $this->setFlashMessage('success', '会员专区设置已保存！');
        }

        return $this->render('VipPlugin:VipAdmin:setting.html.twig', array(
            'vipSetting' => $vipSetting,
        ));
    }

    public function posterUploadAction(Request $request)
    {
        $fileId = $request->request->get('id');
        $objectFile = $this->getFileService()->getFileObject($fileId);

        if (!FileToolkit::isImageFile($objectFile)) {
            throw $this->createAccessDeniedException('图片格式不正确！');
        }

        $file = $this->getFileService()->getFile($fileId);
        $parsed = $this->getFileService()->parseFileUri($file['uri']);

        $vipSetting = $this->getSettingService()->get('vip', array());

        $oldFileId = empty($vipSetting['poster_file_id']) ? null : $vipSetting['poster_file_id'];
        $vipSetting['poster_file_id'] = $fileId;
        $vipSetting['poster'] = "{$this->container->getParameter('topxia.upload.public_url_path')}/" . $parsed['path'];
        $vipSetting['poster'] = ltrim($vipSetting['poster'], '/');

        $this->getSettingService()->set('vip', $vipSetting);

        if ($oldFileId) {
            $this->getFileService()->deleteFile($oldFileId);
        }

        $this->getLogService()->info('system', 'update_settings', '更新浏览器图标', array('poster' => $vipSetting['poster']));

        $response = array(
            'path' => $vipSetting['poster'],
            'url' => $this->container->get('templating.helper.assets')->getUrl($vipSetting['poster']),
        );

        return $this->createJsonResponse($response);
    }

    public function posterRemoveAction(Request $request)
    {
        $setting = $this->getSettingService()->get('vip');
        $setting['poster'] = '';

        $fileId = empty($setting['poster_file_id']) ? null : $setting['poster_file_id'];
        $setting['poster_file_id'] = '';

        $this->getSettingService()->set('vip', $setting);

        if ($fileId) {
            $this->getFileService()->deleteFile($fileId);
        }

        $this->getLogService()->info('system', 'update_settings', '移除站点浏览器图标');

        return $this->createJsonResponse(true);
    }

    protected function getVipService()
    {
        return $this->createService('VipPlugin:Vip:VipService');
    }

    protected function getMessageService()
    {
        return $this->createService('User:MessageService');
    }

    protected function getOrderService()
    {
        return $this->createService('Order:OrderService');
    }

    protected function getLevelService()
    {
        return $this->createService('VipPlugin:Vip:LevelService');
    }

    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }

    protected function getNotificationService()
    {
        return $this->createService('User:NotificationService');
    }

    protected function getFileService()
    {
        return $this->createService('Content:FileService');
    }

    protected function makeMemberLevelOptions($operate_type = array())
    {
        $conditions = $operate_type == 'enabled' ? array('enabled' => 1) : array();
        $levels = $this->getLevelService()->searchLevels(
            $conditions,
            array(),
            0,
            $this->getLevelService()->searchLevelsCount($conditions)
        );

        $options = array();

        foreach ($levels as $level) {
            $options[$level['id']] = $level['name'];
        }

        return $options;
    }
}
