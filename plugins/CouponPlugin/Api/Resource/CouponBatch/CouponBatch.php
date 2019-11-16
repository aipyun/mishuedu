<?php

namespace CouponPlugin\Api\Resource\CouponBatch;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use ApiBundle\Api\Annotation\ApiConf;

class CouponBatch extends AbstractResource
{
    /**
     * @ApiConf(isRequiredAuth=false)
     */
    public function search(ApiRequest $request)
    {
        list($offset, $limit) = $this->getOffsetAndLimit($request);
        $couponBatches = array();
        $total = 0;
        if ($this->isPluginInstalled('coupon')) {
            $conditions = $this->fillCondtions($request->query->all());
            $total = $this->getCouponBatchService()->searchBatchsCount($conditions);
            $couponBatches = $this->getCouponBatchService()->searchBatchs(
                $conditions,
                $this->getSort($request),
                $offset,
                $limit
            );
            foreach ($couponBatches as &$couponBatch) {
                $couponBatch['target'] = null;
                if (in_array($couponBatch['targetType'], array('course', 'classroom')) && !empty($couponBatch['targetId'])) {
                    $type = 'course' == $couponBatch['targetType'] ? 'courseSet' : $couponBatch['targetType'];
                    $this->getOCUtil()->single($couponBatch, array('targetId'), $type);
                }
            }
        }

        return $this->makePagingObject(array_values($couponBatches), $total, $offset, $limit);
    }

    protected function fillCondtions($conditions)
    {
        if (isset($conditions['name'])) {
            $conditions['nameLike'] = $conditions['name'];
            unset($conditions['name']);
        }

        if (isset($conditions['unexpired'])) {
            $conditions['deadlineGt'] = time()-86400;
            unset($conditions['unexpired']);
        }

        return $conditions;
    }

    private function getCouponBatchService()
    {
        return $this->service('CouponPlugin:Coupon:CouponBatchService');
    }
}
