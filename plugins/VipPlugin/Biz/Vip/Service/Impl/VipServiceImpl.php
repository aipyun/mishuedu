<?php

namespace VipPlugin\Biz\Vip\Service\Impl;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\MathToolkit;
use AppBundle\Common\NumberToolkit;
use Biz\BaseService;
use VipPlugin\Biz\Vip\Service\VipService;

class VipServiceImpl extends BaseService implements VipService
{
    public function getMemberByUserId($userId)
    {
        return $this->getMemberDao()->getByUserId($userId);
    }

    public function getVipDetailByUserId($userId)
    {
        return $this->getMemberHistoryDao()->get($userId);
    }

    public function checkMemberName($memberName)
    {
        $avaliable = $this->getUserService()->isNicknameAvaliable($memberName);

        if (!$avaliable) {
            $member = $this->isMemberNameAvaliable($memberName);
            if ($member && $member['deadline'] < time()) {
                return array('error_duplicate', '该用户为过期会员，在过期会员处进行编辑！');
            } elseif ($member) {
                return array('error_duplicate', '该用户已经是会员！');
            }

            return array('success', '');
        }

        return array('error_duplicate', '用户名不存在，请检查！');
    }

    public function searchMembers(array $conditions, array $orderBy, $start, $limit)
    {
        $conditions = $this->processConditions($conditions);

        return $this->getMemberDao()->search($conditions, $orderBy, $start, $limit);
    }

    public function searchMembersCount($conditions)
    {
        $conditions = $this->processConditions($conditions);

        return $this->getMemberDao()->count($conditions);
    }

    public function becomeMember($userId, $levelId, $duration, $unit, $orderId = 0)
    {
        $user = $this->getUserService()->getUser($userId);
        if (empty($user)) {
            throw $this->createServiceException('用户不存在，会员续费失败。');
        }

        $level = $this->getLevelService()->getLevel($levelId);
        if (empty($level) || empty($level['enabled'])) {
            throw $this->createServiceException('会员等级不存在或已关闭，会员续费失败。');
        }

        $duration = intval($duration);
        if (empty($duration)) {
            throw $this->createServiceException('会员开通时长不正确，开通会员失败。');
        }

        //会员升级可以按照天数
        if (!in_array($unit, array('month', 'year', 'day'))) {
            throw $this->createServiceException('会员付费方式不正确，开通会员失败。');
        }

        $order = $this->checkOrderId($orderId);

        $existMember = $this->getMemberByUserId($user['id']);

        $member = array(
            'userId' => $user['id'],
            'levelId' => $level['id'],
            'boughtDuration' => $duration,
            'boughtUnit' => $unit,
            'boughtAmount' => MathToolkit::simple($order['pay_amount'], 0.01),
            'orderId' => $orderId,
            'operatorId' => 0
        );

        $currentUser = $this->getCurrentUser();
        if ($currentUser->id != $member['userId']) {
            $member['operatorId'] = $currentUser['id'];
        }

        if ($existMember) {
            $existMemberLevel = $this->getLevelService()->getLevel($existMember['levelId']);
            if ($level['seq'] > $existMemberLevel['seq']) {
                $member = $this->upgradeMember($member, $existMember);
            } else {
                $member = $this->renewMember($member, $existMember);
            }
        } else {
            $member = $this->createNewMember($member);
        }

        $history = $member;
        $history['priceType'] = 'RMB';

        $this->createMemberHistory($history);

        $member['title'] = $order['title'];
        $this->createOperateRecord($member, 'join', array('member' => $member));

        $this->getLogService()->info('plugin_vip', "{$member['boughtType']}_member", "plugin_vip.{$member['boughtType']}_member", $member);

        if ($order['id'] > 0) {
            $this->dispatchEvent("vip.{$member['boughtType']}", $member);
        } else {
            $this->dispatchEvent('admin.operate.vip_member', $member);
        }

        return $member;
    }

    private function createNewMember($member)
    {
        $filter = array(
            'userId',
            'levelId',
            'boughtDuration',
            'boughtUnit',
            'boughtAmount',
        );
        if (!ArrayToolkit::requireds($member, $filter)) {
            throw $this->createServiceException(' Vip memer arguments invalid');
        }

        $member = $this->filterMember($member);
        $member['deadline'] = $this->getActualDeadline(strtotime("+ {$member['boughtDuration']} {$member['boughtUnit']}s"));
        $member['boughtType'] = 'new';
        $member['boughtTime'] = time();
        $member['deadlineNotified'] = 0;

        return $this->getMemberDao()->create($member);
    }

    private function renewMember($member, $existMember)
    {
        $member = $this->filterMember($member);

        $member['deadline'] = $this->getActualDeadline(strtotime("+ {$member['boughtDuration']} {$member['boughtUnit']}s", max($existMember['deadline'], time())));
        $member['boughtType'] = 'renew';
        $member['boughtTime'] = time();

        return $this->getMemberDao()->update($existMember['id'], $member);
    }

    private function upgradeMember($member, $existMember)
    {
        if (!$this->canUpgradeMember($member['userId'])) {
            throw $this->createServiceException('会员剩余天数小于系统设定的最小可升级天数，不能升级。请续费后再升级。');
        }

        $member = array(
            'levelId' => $member['levelId'],
            'boughtType' => 'upgrade',
            'boughtTime' => time(),
            'boughtAmount' => $member['boughtAmount'],
            'orderId' => $member['orderId'],
        );
        
        return $this->getMemberDao()->update($existMember['id'], $member);
    }
    
    private function getActualDeadline($deadline)
    {
        $date = date('Y-m-d', $deadline);
        //当天的23：59：59失效
        return strtotime($date) + 86400 - 1;
    }

    public function daysBetween($start, $end, $dateUnit = 'day')
    {
        $days = array();
        $result = 1;
        //精确到秒
        $timeUnit = array('day' => 86400, 'hours' => 3600, 'minutes' => 60, 'second' => 1);
        //换算单位
        $dayUnit = array('day' => 1, 'hours' => 24, 'minutes' => 60 * 24, 'second' => 60 * 60 * 24);

        $diff = abs($end - $start);

        foreach ($timeUnit as $key => &$val) {
            $dealedTime = floor($diff / $val);
            $days[$key] = $dealedTime;

            $diff -= ($dealedTime * $val);

            if ($key == $dateUnit) {
                break;
            }
        }

        foreach ($dayUnit as $key => &$val) {
            $result += $days[$key] / $dayUnit[$key];

            if ($key == $dateUnit) {
                break;
            }
        }

        return $result;
    }

    public function calUpgradeMemberAmount($userId, $newLevelId)
    {
        $member = $this->getMemberByUserId($userId);
        if (empty($member)) {
            throw $this->createServiceException('用户不是会员，无法计算升级金额');
        }

        $preLevel = $this->getLevelService()->getLevel($member['levelId']);
        if (empty($preLevel)) {
            throw $this->createServiceException('原始会员等级不存在，无法计算升级金额');
        }

        $level = $this->getLevelService()->getLevel($newLevelId);
        if (empty($level)) {
            throw $this->createServiceException('会员等级不存在，无法计算升级金额');
        }
        
        /*$cashRate = 1;
        $priceType = 'RMB';
        $coinSetting = $this->getSettingService()->get('coin');
        if (!empty($coinSetting['coin_enabled'] == 1)) {
            $cashRate = empty($coinSetting['cash_rate']) ? 1 : $coinSetting['cash_rate'];
            $priceType = empty($coinSetting['price_type']) ? 'RMB' : $coinSetting['price_type'];
        }*/

        if ($member['boughtUnit'] == 'month') {
            $unitAmount = $this->daysBetween($member['deadline'], time()) / 30;

            $preLevelPrice = $preLevel['monthPrice'];
            $levelPrice = $level['monthPrice'];
        } else {
            $unitAmount = $this->daysBetween($member['deadline'], time()) / 365;

            $preLevelPrice = $preLevel['yearPrice'];
            $levelPrice = $level['yearPrice'];
        }

        /*if ($priceType == 'Coin') {
            $preLevelPrice = NumberToolkit::roundUp($preLevelPrice * $cashRate);
            $levelPrice = NumberToolkit::roundUp($levelPrice * $cashRate);
        }*/

        $amount = ($levelPrice - $preLevelPrice) * $unitAmount;

        if ($amount < 0) {
            $amount = 0.00;
        }

        return $amount;
    }

    public function updateMemberInfo($userId, array $fields)
    {
        $member = $this->getMemberDao()->getByUserId($userId);

        if (empty($member)) {
            throw $this->createNotFoundException('member not exists!');
        }

        $memberData['levelId'] = $fields['levelId'];
        $memberData['deadline'] = $this->getActualDeadline(strtotime($fields['deadline']));
        $memberData['boughtType'] = 'edit';
        $memberData['boughtUnit'] = $fields['boughtUnit'];

        $member = $this->getMemberDao()->update($member['id'], $memberData);

        $history = $member;
        unset($history['id']);
        unset($history['deadlineNotified']);
        $this->getMemberHistoryDao()->create($history);

        $this->getLogService()->info('vip', 'update_member', '编辑会员', $member);

        $this->dispatchEvent(
            'admin.operate.vip_member',
            $member
        );

        return $member;
    }

    public function updateMembers($userIds, $fields)
    {
        if (!$userIds) {
            return false;
        }

        foreach ($userIds as $userId) {
            $this->updateMemberInfo($userId, $fields);
        }

        return true;
    }

    public function cancelMemberByUserId($userId)
    {
        $member = $this->getMemberDao()->getByUserId($userId);
        $level = $this->getLevelService()->getLevel($member['levelId']);

        $historyData['createdTime'] = $member['createdTime'];
        $historyData['boughtType'] = 'cancel';
        $historyData['userId'] = $member['userId'];
        $historyData['levelId'] = $member['levelId'];
        $historyData['deadline'] = $member['deadline'];
        $memberHistory = $this->createMemberHistory($historyData);

        $this->getMemberDao()->deleteByUserId($member['userId']);
        $user = $this->getUserService()->getUser($member['userId']);

        $this->getLogService()->info('vip', 'delete_member', "管理员删除会员资料 {$user['nickname']} (#{$memberHistory['userId']})", $historyData);

        $levelName = $level ? $level['name'] : '会员';
        $message = "您已经被取消 {$levelName}，如有疑问请联系管理员";
        $this->dispatchEvent('vip.cancel', $member);
        $this->getNotificationService()->notify($userId, 'default', $message);
        
        $member['title'] = $levelName;
        $this->createOperateRecord($member, 'exit', $member);

        return true;
    }

    public function cancelMembers($userIds)
    {
        if (empty($userIds)) {
            return false;
        }

        foreach ($userIds as $userId) {
            $this->cancelMemberByUserId($userId);
        }

        return true;
    }

    public function searchMembersHistoriesCount($conditions)
    {
        $newConditions = $this->processConditions($conditions);

        return $this->getMemberHistoryDao()->count($newConditions);
    }

    public function searchMembersHistories(array $conditions, array $orderBy, $start, $limit)
    {
        $newConditions = $this->processConditions($conditions);

        return $this->getMemberHistoryDao()->search($newConditions, $orderBy, $start, $limit);
    }

    public function checkUserInMemberLevel($userId, $levelId)
    {
        $setting = $this->getSettingService()->get('vip');

        if (empty($setting['enabled'])) {
            return 'vip_closed';
        }

        if (empty($userId)) {
            return 'not_login';
        }

        $member = $this->getMemberByUserId($userId);

        if (empty($member)) {
            return 'not_member';
        }

        if (strtotime(date('Y-m-d', $member['deadline'])) < strtotime(date('Y-m-d', time()))) {
            return 'member_expired';
        }

        $memberLevel = $this->getLevelService()->getLevel($member['levelId']);

        if (empty($memberLevel)) {
            return 'level_not_exist';
        }

        if (empty($levelId)) {
            return 'level_not_exist';
        }

        $level = $this->getLevelService()->getLevel($levelId);

        if (empty($level)) {
            return 'level_not_exist';
        }

        if ($memberLevel['seq'] < $level['seq']) {
            return 'level_low';
        }

        return 'ok';
    }

    public function updateDeadlineNotified($vipId, $deadlineNotifyStatus)
    {
        $member = $this->getMemberDao()->get($vipId);

        if (empty($member)) {
            throw $this->createNotFoundException('member not exists!');
        }

        $this->getMemberDao()->update($vipId, array(
            'deadlineNotified' => $deadlineNotifyStatus,
        ));
    }

    protected function processConditions($conditions)
    {
        $type = empty($conditions['type']) ? '' : $conditions['type'];

        if (!empty($conditions['nickname'])) {
            $users = $this->getUserService()->searchUsers(array('nickname' => $conditions['nickname']), array('createdTime' => 'DESC'), 0, PHP_INT_MAX);
            $conditions['userIds'] = empty($users) ? array(-1) : ArrayToolkit::column($users, 'id');
            unset($conditions['nickname']);
        }

        if (!empty($conditions['startDateTime'])) {
            $conditions['deadline_GTE'] = strtotime($conditions['startDateTime']);
        }

        if (!empty($conditions['endDateTime'])) {
            $conditions['deadline_LTE'] = strtotime($conditions['endDateTime']);
        }

        if ($type == 'will_expire') {
            $conditions['deadline_GTE'] = time();
            $conditions['deadline_LT'] = time() + 24 * 60 * 60 * 10;
        } elseif ($type == 'just_expire') {
            $conditions['deadline_LT'] = time();
        }

        return $conditions;
    }

    private function createMemberHistory($memberHistoyData)
    {
        if (empty($memberHistoyData)) {
            return array();
        }

        $filter = array(
            'userId',
            'levelId',
            'deadline',
            'boughtType',
            'boughtTime',
            'boughtDuration',
            'boughtUnit',
            'boughtAmount',
            'orderId',
            'operatorId',
            'priceType'
        );

        $memberHistoyData = ArrayToolkit::parts($memberHistoyData, $filter);

        return $this->getMemberHistoryDao()->create($memberHistoyData);
    }

    private function isMemberNameAvaliable($memberName)
    {
        $user = $this->getUserService()->getUserByNickname($memberName);
        $condition['userId'] = $user['id'];
        $members = $this->searchMembers($condition, array('createdTime' => 'DESC'), 0, 1);

        return $members ? $members[0] : array();
    }

    public function canUpgradeMember($userId)
    {
        $vip = $this->getMemberByUserId($userId);

        if (empty($vip)) {
            return true;
        }

        return $this->checkUpgradeMinDay($vip['deadline']);
    }

    public function canJoinVip($vipLevelId)
    {
        $vipLevel = $this->getLevelService()->getLevel($vipLevelId);
        if (empty($vipLevel)) {
            return array(
                'code' => 'vip.not_found',
                'msg' => '该会员等级不存在',
            );
        }

        $user = $this->getCurrentUser();

        $member = $this->getMemberByUserId($user['id']);
        if (empty($member)) {
            return array('code' => 'success');
        }

        $memberLevel = $this->getLevelService()->getLevel($member['levelId']);
        if ($memberLevel['seq'] > $vipLevel['seq']) {
            return array(
                'code' => 'vip.member_exists',
                'msg' => '已经是该等级会员了',
            );
        } elseif ($memberLevel['seq'] < $vipLevel['seq']) {
            $canUpgrad = $this->checkUpgradeMinDay($member['deadline']);
            $setting = $this->getSettingService()->get('vip', array());
            
            if (!$canUpgrad) {
                return array(
                    'code' => 'vip.upgrade_fail',
                    'msg' => "会员剩余天数小于{$setting['upgrade_min_day']}天，请先续费后再升级",
                );
            }
        }

        return array('code' => 'success');
    }

    protected function checkUpgradeMinDay($memberDeadline)
    {
        $setting = $this->getSettingService()->get('vip', array());
        if (empty($setting['upgrade_min_day'])) {
            return true;
        }

        $diff = strtotime(date('Y-m-d', $memberDeadline)) - strtotime(date('Y-m-d', time()));
        $upgradMinDaySeconds = $setting['upgrade_min_day'] * 86400;

        return $diff >= $upgradMinDaySeconds;
    }

    protected function checkOrderId($orderId)
    {
        $orderId = intval($orderId);

        if (empty($orderId)) {
            return array(
                'id' => 0,
                'title' => '',
                'pay_amount' => 0,
                'price_amount' => 0,
                'price_type' => 'RMB'
            );
        }

        $order = $this->getOrderService()->getOrder($orderId);
        if (empty($order)) {
            throw $this->createServiceException('开通会员的订单不存在，开通会员失败。');
        }

        return $order;
    }

    protected function filterMember($member)
    {
        $filter = array(
            'userId',
            'levelId',
            'deadline',
            'boughtType',
            'boughtTime',
            'boughtDuration',
            'boughtUnit',
            'boughtAmount',
            'orderId',
            'operatorId',
            'deadlineNotified'
        );

        return ArrayToolkit::parts($member, $filter);
    }

    protected function createOperateRecord($member, $operateType, $data = array())
    {
        $currentUser = $this->getCurrentUser();
        $operatorId = $currentUser['id'] != $member['userId'] ? $currentUser['id'] : 0;

        $record = array(
            'user_id' => $member['userId'],
            'order_id' => $member['orderId'],
            'member_id' => $member['id'],
            'target_id' => $member['levelId'],
            'target_type' => 'vip',
            'operate_type' => $operateType,
            'operate_time' => time(),
            'operator_id' => $operatorId,
            'data' => $data,
            'title' => $member['title'],
        );

        return $this->getMemberOperationService()->createRecord($record);
    }

    /**
     * @return VipDao
     */
    private function getMemberDao()
    {
        return $this->createDao('VipPlugin:Vip:VipDao');
    }

    private function getMemberHistoryDao()
    {
        return $this->createDao('VipPlugin:Vip:VipHistoryDao');
    }

    private function getOrderService()
    {
        return $this->createService('Order:OrderService');
    }

    private function getLevelService()
    {
        return $this->createService('VipPlugin:Vip:LevelService');
    }

    private function getUserService()
    {
        return $this->createService('User:UserService');
    }

    protected function getLogService()
    {
        return $this->createService('System:LogService');
    }

    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }

    protected function getNotificationService()
    {
        return $this->createService('User:NotificationService');
    }

    protected function getMemberOperationService()
    {
        return $this->biz->service('MemberOperation:MemberOperationService');
    }
}
