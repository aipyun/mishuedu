<?php
namespace Topxia\Service\System\Tests;

use Topxia\Service\Common\BaseTestCase;

// TODO

class CouponBatchServiceTest extends BaseTestCase
{
    public function testGetBatch()
    {
        $batchArray = array(
            'name'         => 'testCoupon',
            'prefix'       => '123',
            'type'         => 'discount',
            'rate'         => 22,
            'generatedNum' => 3,
            'digits'       => '8ss',
            'deadline'     => 1446566400,
            'targetType'   => 'course',
            'targetId'     => 0
        );
        $batchId = 1;
        // $this->getCouponBatchService()->generateCoupon($batchArray);
        // $batch = $this->getCouponBatchService()->getBatch($batchId);
        // $this->getCouponBatchService()->findBatchsByIds(array(1));
        // $coupons    = $this->getCouponService()->findCouponsByBatchId($batchId, 0, 999);
        // $conditions = array();
        // $this->getCouponService()->searchCoupons($conditions, array('createdTime', 'DESC'), 0, 2);
        // $this->getCouponService()->getBatchByToken($batch['token'], false);
        // $count       = $this->getCouponService()->searchCouponsCount($conditions);
        // $batchCount  = $this->getCouponBatchService()->searchBatchsCount($conditions);
        // $batchs      = $this->getCouponBatchService()->searchBatchs($conditions, array('createdTime', 'DESC'), 0, 99);
        // $deleteBatch = $this->getCouponBatchService()->deleteBatch($batchId);
        // $batch       = $this->getCouponBatchService()->checkBatchPrefix('123');
        // $coupon      = $this->getCouponBatchService()->getCouponByCode($coupons[1]['code']);
        // $result      = $this->getCouponService()->checkCouponUseable($coupon['code'], $coupon['targetType'], $coupon['targetId'], $coupon['rate']);
        // $order       = array(
        //     'userId'     => 1,
        //     'title'      => '订单',
        //     'amount'     => 22,
        //     'targetType' => 'course',
        //     'targetId'   => 0,
        //     'payment'    => 'alipay',
        //     'couponCode' => $coupon['code']
        // );
        // $order         = $this->getOrderService()->createOrder($order);
        // $useResult     = $this->getCouponService()->useCoupon($coupon['code'], $order);
        // $batch         = $this->getCouponBatchService()->getBatch($batchId);
        // $receiveResult = $this->getCouponBatchService()->receiveCoupon($batch['token'], 1);
    }

    protected function getUserService()
    {
        return $this->getServiceKernel()->createService('User.UserService');
    }

    protected function getCouponService()
    {
        return $this->getServiceKernel()->createService('Coupon:CouponService');
    }

    protected function getCouponBatchService()
    {
        return $this->getServiceKernel()->createService('CouponPlugin:Coupon:CouponBatchService');
    }

    private function getTokenService()
    {
        return $this->getServiceKernel()->createService('User:TokenService');
    }

    private function getOrderService()
    {
        return $this->getServiceKernel()->createService('Order:OrderService');
    }

    private function getSettingService()
    {
        return $this->getServiceKernel()->createService('System.SettingService');
    }

    private function getLogService()
    {
        return $this->getServiceKernel()->createService('System.LogService');
    }

}
