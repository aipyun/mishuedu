<?php

namespace VipPlugin\Biz\OrderFacade\Product;

use Biz\OrderFacade\Exception\OrderPayCheckException;
use Biz\OrderFacade\Product\Product;
use AppBundle\Common\ArrayToolkit;
use Codeages\Biz\Order\Status\OrderStatusCallback;

class VipProduct extends Product implements OrderStatusCallback
{
    const TYPE = 'vip';
    private $params = array();

    public $targetType = self::TYPE;

    public $showTemplate = 'VipPlugin:order:vip-item.html.twig';

    public $buyType = '';

    public function init(array $params)
    {
        $vipId = $params['targetId'];
        $vipLevel = $this->getVipLevelService()->getLevel($vipId);
        if (!empty($params['orderItemId'])) {
            $orderItem = $this->getOrderService()->getOrderItem($params['orderItemId']);
            $params = array_merge($params, ArrayToolkit::parts($orderItem, array('unit', 'num')));
        }
        $this->title = $vipLevel['name'];
        $this->targetId = $vipLevel['id'];
        $this->backUrl = array('routing'=>'vip','params' => array());
        $this->picture = $vipLevel['icon'];
        $this->num = empty($params['num']) ? $this->num : $params['num'];
        $this->unit = empty($params['unit']) ? $this->unit : $params['unit'];
        $this->setBuyType($this->targetId);

        $this->originPrice = $this->getOriginPrice($vipId, $this->unit, $this->num);
        $this->successUrl = array('vip', array());
        $this->maxRate = $vipLevel['maxRate'];
        $this->cover = array(
            'small' => $vipLevel['icon'],
            'middle' => $vipLevel['icon'],
            'large' => $vipLevel['icon'],
        );
    }

    public function validate()
    {
        //新进用户、续费用户需要时间区间； 升级不需要时间区间，由product内部定义好
        $allowUnits = $this->buyType == 'upgrade' ? array('day') : array('month', 'year');

        if (!preg_match('/^\d+$/', $this->num) || $this->num < 1 || !in_array($this->unit, $allowUnits)) {
            throw new OrderPayCheckException('Argument invalid', Product::PRODUCT_VALIDATE_FAIL);
        }
        
        $access = $this->getVipService()->canJoinVip($this->targetId);

        if ($access['code'] !== 'success') {
            throw new OrderPayCheckException($access['msg'], Product::PRODUCT_VALIDATE_FAIL);
        }
    }

    public function onSuccess($orderItem)
    {
        if (empty($orderItem['refund_deadline'])) {
            return;
        }

        try {
            $this->getWorkflowService()->finished($orderItem['order_id']);
        } catch (\Exception $e) {
            $this->getLogService()->error(
                'order',
                'vip_callback',
                'order.vip_callback.fail',
                array('error' => $e->getMessage(), 'context' => $orderItem)
            );
        }
    }

    public function onPaid($orderItem)
    {
        $this->smsCallback($orderItem, '会员');

        $unit = $orderItem['unit'];
        $duration = $orderItem['num'];
        $userId = $orderItem['user_id'];

        $vipLevel = $this->getVipLevelService()->getLevel($orderItem['target_id']);

        try {
            $this->getVipService()->becomeMember($userId, $vipLevel['id'], $duration, $unit, $orderItem['order_id']);
            
            return OrderStatusCallback::SUCCESS;
        } catch (\Exception $e) {
            $this->getLogService()->error(
                'order',
                'vip_callback',
                'order.vip_callback.fail',
                array('error' => $e->getMessage(), 'context' => $orderItem)
            );

            return false;
        }
    }

    public function getCreateExtra()
    {
        return array('buyType' => $this->buyType);
    }

    private function getOriginPrice($vipLevelId, $unit, $num)
    {
        $user = $this->getUser();
        $vipLevel = $this->getVipLevelService()->getLevel($vipLevelId);

        $existMember = $this->getVipService()->getMemberByUserId($user['id']);
        if ($existMember) {
            $existMemberLevel = $this->getVipLevelService()->getLevel($existMember['levelId']);
            if ($vipLevel['seq'] > $existMemberLevel['seq']) {
                return $this->getVipService()->calUpgradeMemberAmount($user['id'], $vipLevelId);
            }
        }

        $levelPrice = array(
            'month' => $vipLevel['monthPrice'],
            'year' => $vipLevel['yearPrice'],
        );

        $unitPrice = empty($levelPrice[$unit]) ? 0 : $levelPrice[$unit];

        return $unitPrice * $num;
    }

    private function setBuyType($vipLevelId)
    {
        $user = $this->getUser();
        $vipLevel = $this->getVipLevelService()->getLevel($vipLevelId);

        $existMember = $this->getVipService()->getMemberByUserId($user['id']);

        if ($existMember) {
            $existMemberLevel = $this->getVipLevelService()->getLevel($existMember['levelId']);
            if ($vipLevel['seq'] > $existMemberLevel['seq']) {
                $this->buyType = 'upgrade';
                $this->num = round(($existMember['deadline'] - time()) / 86400);
                $this->unit = 'day';
            } else {
                $this->buyType = 'renew';
            }
        } else {
            $this->buyType = 'new';
        }
    }

    protected function getVipLevelService()
    {
        return $this->biz->service('VipPlugin:Vip:LevelService');
    }

    private function getWorkflowService()
    {
        return $this->biz->service('Order:WorkflowService');
    }

    protected function getVipService()
    {
        return $this->biz->service('VipPlugin:Vip:VipService');
    }

    protected function getOrderService()
    {
        return $this->biz->service('Order:OrderService');
    }

    protected function getUser()
    {
        $biz = $this->biz;
        return $biz['user'];
    }
}
