<?php

namespace CouponPlugin\Biz\Coupon\Service\Impl;

use AppBundle\Common\ArrayToolkit;
use Biz\BaseService;
use CouponPlugin\Biz\Coupon\Service\CouponBatchService;
use Codeages\Biz\Framework\Dao\BatchCreateHelper;

class CouponBatchServiceImpl extends BaseService implements CouponBatchService
{
    public function getBatch($id)
    {
        return $this->getCouponBatchDao()->get($id);
    }

    public function findBatchsByIds(array $ids)
    {
        $batchs = $this->getCouponBatchDao()->findBatchsByIds($ids);

        return ArrayToolkit::index($batchs, 'id');
    }

    public function getBatchByToken($token, $locked = false)
    {
        return $this->getCouponBatchDao()->getBatchByToken($token, $locked);
    }

    public function updateUnreceivedNumByBatchId($batchId)
    {
        $batch = $this->getBatch($batchId);
        if (empty($batch)) {
            throw $this->createServiceException('优惠券批次不存在');
        }

        $unreceivedNum = $this->getCouponService()->searchCouponsCount(array(
            'userId' => 0,
            'batchId' => $batch['id'],
        ));
        $this->getCouponBatchDao()->update($batchId, array('unreceivedNum' => $unreceivedNum));
    }

    public function generateCoupon($couponData)
    {
        $couponData = array_filter($couponData);
        $couponData['h5MpsEnable'] = isset($couponData['h5MpsEnable']) ?: 0;
        $couponData['linkEnable'] = isset($couponData['linkEnable']) ?: 0;
        $couponData['codeEnable'] = isset($couponData['codeEnable']) ?: 0;

        $batchArray = array(
            'name',
            'prefix',
            'type',
            'rate',
            'generatedNum',
            'digits',
            'deadline',
            'targetType',
            'h5MpsEnable',
            'linkEnable',
            'codeEnable', );
        $batch = ArrayToolkit::parts($couponData, $batchArray);

        if (!ArrayToolkit::requireds($couponData, $batchArray)) {
            throw $this->createServiceException('缺少必要参数，生成优惠码失败');
        }

        if (0 == $batch['h5MpsEnable'] && 0 == $batch['linkEnable'] && 0 == $batch['codeEnable']) {
            throw $this->createServiceException('至少选择一个发放渠道');
        }

        $batch['deadline'] = strtotime($batch['deadline']);
        $batch['createdTime'] = time();
        $batch['unreceivedNum'] = $batch['generatedNum'];

        if ('fullDiscount' == $couponData['targetType']) {
            if (!isset($couponData['fullDiscountPrice'])) {
                throw $this->createServiceException('缺少必要参数，生成优惠码失败');
            }

            if (!$couponData['fullDiscountPrice'] > 0) {
                throw $this->createServiceException('满减条件金额必须大于0');
            }

            $batch['fullDiscountPrice'] = $couponData['fullDiscountPrice'];
        }

        if (isset($couponData['targetId'])) {
            $batch['targetId'] = $couponData['targetId'];
        }

        if (isset($couponData['description'])) {
            $batch['description'] = $couponData['description'];
        }

        if ($batch['deadline'] + 86400 < $batch['createdTime']) {
            throw $this->createServiceException('优惠码有效期不能比当前日期晚！');
        }

        $token = $this->getTokenService()->makeToken('coupon', array(
            'duration' => $batch['deadline'] + 86400 - time(),
        ));

        $batch['token'] = $token['token'];

        try {
            $batch = $this->getCouponBatchDao()->create($batch);
        } catch (\Exception $e) {
            throw $this->createServiceException($e->getMessage());
        }

        return $batch;
    }

    public function searchBatchsCount(array $conditions)
    {
        return $this->getCouponBatchDao()->count($conditions);
    }

    public function searchBatchs(array $conditions, $orderBy, $start, $limit)
    {
        $batchs = $this->getCouponBatchDao()->search($conditions, $orderBy, $start, $limit);

        return ArrayToolkit::index($batchs, 'id');
    }

    public function deleteBatch($id)
    {
        if (empty($id)) {
            throw $this->createServiceException(sprintf('优惠码批次不存在或已被删除'));
        }

        $coupons = $this->getCouponService()->findCouponsByBatchId($id, 0, 1000);

        $this->getCouponService()->deleteCouponsByBatch($id);
        $this->getCouponBatchDao()->delete($id);

        foreach ($coupons as $coupon) {
            $card = $this->getCardService()->getCardByCardIdAndCardType($coupon['id'], 'coupon');

            if (!empty($card)) {
                $this->getCardService()->updateCardByCardIdAndCardType($coupon['id'], 'coupon', array('status' => 'deleted'));

                if ('minus' == $coupon['type']) {
                    $message = '您的一张价值为￥'.$coupon['rate'].'的优惠券已经被管理员删除，详情请联系管理员。';
                } else {
                    $message = '您的一张折扣为'.$coupon['rate'].'折的优惠券已经被管理员删除，详情请联系管理员。';
                }

                $this->getNotificationService()->notify($card['userId'], 'default', $message);
            }
        }
    }

    public function checkBatchPrefix($prefix)
    {
        if (empty($prefix)) {
            return false;
        }

        $prefix = $this->getCouponBatchDao()->findBatchByPrefix($prefix);

        return empty($prefix) ? true : false;
    }

    public function receiveCoupon($token, $userId)
    {
        $token = $this->getTokenService()->verifyToken('coupon', $token);

        if (!$token) {
            return array(
                'code' => 'failed',
                'message' => '无效的链接',
            );
        }
        $user = $this->getUserService()->getUser($userId);
        if (!$user) {
            return array(
                'code' => 'failed',
                'message' => '请登录网校账号后再领取',
            );
        }
        try {
            $this->getCouponBatchDao()->db()->beginTransaction();
            $batch = $this->getCouponBatchDao()->getBatchByToken($token['token'], true);

            if (empty($batch)) {
                $this->getCouponBatchDao()->db()->commit();

                return array(
                    'code' => 'failed',
                    'message' => '链接不存在或已被删除',
                );
            }

            if (!empty($userId)) {
                $conditions = array(
                    'userId' => $userId,
                    'batchId' => $batch['id'],
                );
                $coupon = $this->getCouponService()->searchCoupons($conditions, array('id' => 'DESC'), 0, 1);

                if (!empty($coupon)) {
                    $this->getCouponBatchDao()->db()->commit();

                    return array(
                        'code' => 'failed',
                        'message' => '您已经领取该批优惠码',
                    );
                }
            }

            $conditions = array(
                'userId' => 0,
                'batchId' => $batch['id'],
            );
            $coupons = $this->getCouponService()->searchCoupons($conditions, array('id' => 'ASC'), 0, 1);

            if (empty($coupons)) {
                $this->getCouponBatchDao()->db()->commit();

                return array(
                    'code' => 'failed',
                    'message' => '该批优惠码已经被领完',
                );
            }

            $couponsId = ArrayToolkit::column($coupons, 'id');
            $coupon = $this->getCouponService()->getCoupon($couponsId[0]);

            if (!empty($userId) && !empty($coupon)) {
                $coupon = $this->getCouponService()->updateCoupon($coupon['id'], array(
                    'userId' => $userId,
                    'status' => 'receive',
                    'receiveTime' => time(),
                ));

                if (empty($coupon)) {
                    $this->getCouponBatchDao()->db()->commit();

                    return array(
                        'code' => 'failed',
                        'message' => '优惠码领取失败',
                    );
                }

                $this->getCardService()->addCard(array(
                    'cardType' => 'coupon',
                    'cardId' => $coupon['id'],
                    'deadline' => $coupon['deadline'],
                    'userId' => $userId,
                ));

                if ('minus' == $coupon['type']) {
                    $message = '您有一张价值'.$coupon['rate'].'元的优惠券领取成功';
                } else {
                    $message = '您有一张抵扣为'.$coupon['rate'].'折的优惠券领取成功';
                }

                $this->getNotificationService()->notify($userId, 'default', $message);
                $this->getLogService()->info('coupon', 'receive', "领取了优惠券 {$coupon['code']}", $coupon);
            }

            $this->updateUnreceivedNumByBatchId($batch['id']);
            $this->getCouponBatchDao()->db()->commit();

            return array(
                'id' => $coupon['id'],
                'code' => 'success',
                'message' => '领取成功，请在卡包中查看',
            );
        } catch (\Exception $e) {
            $this->getCouponBatchDao()->db()->rollback();
            throw $e;
        }
    }

    public function updateBatch($id, $fields)
    {
        return $this->getCouponBatchDao()->update($id, $fields);
    }

    public function sumDeductAmountByBatchId($batchId)
    {
        return $this->getCouponBatchDao()->sumDeductAmountByBatchId($batchId);
    }

    public function createBatchCoupons($batchId, $generatedNum = 0)
    {
        $batch = $this->getBatch($batchId);
        if (empty($batch)) {
            return null;
        }

        $generated = $this->getCouponService()->searchCouponsCount(array('batchId' => $batch['id']));
        $remain = intval($batch['generatedNum'] - $generated);
        if ($remain < $generatedNum) {
            $generatedNum = $remain;
        }

        $couponDao = $this->getCouponDao();
        $batchCreateHelper = $this->getBatchCreateHelper($couponDao);
        try {
            $time = time();
            for ($i = 0; $i < $generatedNum; ++$i) {
                $couponCode = $this->generateRandomCode($batch['digits'], $batch['prefix']);

                $coupon = array(
                    'code' => $couponCode,
                    'type' => $batch['type'],
                    'status' => 'unused',
                    'rate' => $batch['rate'],
                    'batchId' => $batch['id'],
                    'deadline' => $batch['deadline'],
                    'targetType' => $batch['targetType'],
                    'targetId' => $batch['targetId'],
                    'fullDiscountPrice' => $batch['fullDiscountPrice'],
                    'createdTime' => $time,
                );
                $batchCreateHelper->add($coupon);
            }
            $batchCreateHelper->flush();
        } catch (\Exception $e) {
            throw $this->createServiceException($e->getMessage());
        }

        return true;
    }

    public function searchH5MpsBatches($conditions, $offset, $limit)
    {
        $this->fullSearchH5MpsBatchesConditions($conditions);

        $batches = $this->getCouponBatchDao()->searchH5MpsBatches($conditions, $offset, $limit);

        return ArrayToolkit::index($batches, 'id');
    }

    public function fillUserCurrentCouponByBatches($batches)
    {
        $batches = ArrayToolkit::index($batches, 'id');
        $user = $this->getCurrentUser();
        if (!empty($user['id']) && !empty($batches)) {
            $conditions = array(
                'batchIds' => array_keys($batches),
                'userId' => $user['id'],
            );

            $receivedCoupons = $this->getCouponService()->searchCoupons(
                $conditions,
                array(),
                0,
                $this->getCouponService()->searchCouponsCount($conditions)
            );
            foreach ($receivedCoupons as $coupon) {
                //如果当前批次已有且当前循环的优惠券已使用时不做覆盖
                if (!empty($batches[$coupon['batchId']]['currentUserCoupon']) && 'receive' != $coupon['status']) {
                    continue;
                }
                $batches[$coupon['batchId']]['currentUserCoupon'] = $coupon;
            }
        }

        return $batches;
    }

    public function countH5MpsBatches($conditions)
    {
        $this->fullSearchH5MpsBatchesConditions($conditions);

        return $this->getCouponBatchDao()->countH5MpsBatches($conditions);
    }

    protected function fullSearchH5MpsBatchesConditions(&$conditions)
    {
        $user = $this->getCurrentUser();

        if (!ArrayToolkit::requireds($conditions, array('targetType', 'targetId'))) {
            throw $this->createInvalidArgumentException('Lack of required fields.');
        }

        $conditions = ArrayToolkit::parts($conditions, array('targetType', 'targetId'));

        if (empty($user['id'])) {
            $conditions['h5MpsEnable'] = 1;
            $conditions['deadlineGt'] = time();
            $conditions['unreceivedNumGt'] = '0';

            $conditions['targetTypes'] = array('all', $conditions['targetType']);
            unset($conditions['targetType']);

            $conditions['targetIds'] = array(0, $conditions['targetId']);
            unset($conditions['targetId']);
        } else {
            $conditions['userId'] = $user['id'];
        }
    }

    private function generateRandomCode($length, $prefix)
    {
        $randomCode = base_convert(sha1(uniqid(mt_rand(), true)), 16, 36);

        $code = substr($randomCode, 0, $length);

        $code = $prefix.strtoupper($code);

        $existsCode = $this->getCouponService()->getCouponByCode($code);

        if ($existsCode) {
            return $this->generateRandomCode($length, $prefix);
        } else {
            return $code;
        }
    }

    private function getBatchCreateHelper($dao)
    {
        return new BatchCreateHelper($dao);
    }

    private function getTokenService()
    {
        return $this->createService('User:TokenService');
    }

    private function getOrderService()
    {
        return $this->createService('Order:OrderService');
    }

    private function getCardService()
    {
        return $this->createService('Card:CardService');
    }

    private function getCouponService()
    {
        return $this->createService('Coupon:CouponService');
    }

    private function getCouponDao()
    {
        return $this->createDao('Coupon:CouponDao');
    }

    private function getCouponBatchDao()
    {
        return $this->createDao('CouponPlugin:Coupon:CouponBatchDao');
    }

    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }

    private function getLogService()
    {
        return $this->createService('System:LogService');
    }

    private function getNotificationService()
    {
        return $this->createService('User:NotificationService');
    }

    private function getUserService()
    {
        return $this->createService('User:UserService');
    }
}
