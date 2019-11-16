<?php

namespace CouponPlugin\Api\Resource\CouponBatch;

use ApiBundle\Api\Resource\Filter;
use ApiBundle\Api\Resource\CourseSet\CourseSetFilter;
use ApiBundle\Api\Resource\Classroom\ClassroomFilter;
use ApiBundle\Api\Resource\Coupon\CouponFilter;

class CouponBatchFilter extends Filter
{
    protected $publicFields = array(
        'id', 'name', 'token', 'type', 'prefix', 'generatedNum', 'usedNum', 'receiveNum', 'rate', 'deadline', 'unreceivedNum', 'currentUserCoupon', 'target', 'targetType', 'description', 'createdTime',
    );

    protected function publicFields(&$data)
    {
        $data['deadline'] = date('c', $data['deadline']);
        if (in_array($data['targetType'], array('course', 'classroom')) && !empty($data['target'])) {
            $targetFilter = $this->getFilter($data['targetType']);
            $targetFilter->setMode(Filter::SIMPLE_MODE);
            $targetFilter->filter($data['target']);
        }

        if (isset($data['currentUserCoupon'])) {
            $couponFilter = new CouponFilter(Filter::PUBLIC_MODE);
            $couponFilter->filter($data['currentUserCoupon']);
        } else {
            $data['currentUserCoupon'] = null;
        }
    }

    protected function getFilter($type)
    {
        $filters = array(
            'course' => new CourseSetFilter(),
            'classroom' => new ClassroomFilter(),
        );

        return $filters[$type];
    }
}
