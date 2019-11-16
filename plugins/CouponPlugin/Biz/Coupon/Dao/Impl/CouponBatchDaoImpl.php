<?php

namespace CouponPlugin\Biz\Coupon\Dao\Impl;

use Codeages\Biz\Framework\Dao\GeneralDaoImpl;
use CouponPlugin\Biz\Coupon\Dao\CouponBatchDao;

class CouponBatchDaoImpl extends GeneralDaoImpl implements CouponBatchDao
{
    protected $table = 'coupon_batch';

    public function declares()
    {
        return array(
            'serializes' => array(),
            'orderbys' => array('createdTime', 'id'),
            'timestamps' => array('createdTime'),
            'conditions' => array(
                'targetId = :targetId',
                'targetId IN (:targetIds)',
                'targetType = :targetType',
                'targetType IN (:targetTypes)',
                'name LIKE :nameLike',
                'type = :type',
                'unreceivedNum > :unreceivedNumGt',
                'h5MpsEnable = :h5MpsEnable',
                'deadline > :deadlineGt',
            ),
        );
    }

    public function findBatchsByIds($ids)
    {
        return $this->findInField('id', $ids);
    }

    public function getBatchByToken($token, $locked = false)
    {
        $sql = "SELECT * FROM {$this->table()} WHERE token = ?".($locked ? ' FOR UPDATE' : '');

        return $this->db()->fetchAssoc($sql, array($token)) ?: null;
    }

    public function sumDeductAmountByBatchId($batchId)
    {
        $sql = "SELECT sum(od.`deduct_amount`) FROM `biz_order_item_deduct` as od left join `coupon` as c on od.deduct_id = c.id where od.`deduct_type` = 'coupon' and c.`batchId`= ? and c.status ='used'";
        $sum = $this->db()->fetchColumn($sql, array($batchId));

        return empty($sum) ? 0 : $sum;
    }

    public function searchH5MpsBatches($conditions, $offset, $limit)
    {
        $this->filterStartLimit($offset, $limit);
        if (isset($conditions['userId'])) {
            $sql = "
                SELECT DISTINCT(coupon_batch.id), coupon_batch.*
                FROM coupon_batch coupon_batch
                    LEFT JOIN (
                        SELECT userId, batchId, status
                        FROM coupon
                        WHERE userId = ?
                    ) coupon
                    ON coupon_batch.id = coupon.batchId
                WHERE (coupon_batch.h5MpsEnable = 1)
                    AND ((coupon.userId IS NULL AND coupon_batch.unreceivedNum > 0) OR (coupon.status = 'receive'))
                    AND (coupon_batch.deadline > ?)
                    AND (coupon_batch.targetId IN (0, ?))
                    AND (coupon_batch.targetType IN ('all', ?))
                ORDER BY coupon_batch.id DESC
                LIMIT {$offset}, {$limit}
            ";

            return $this->db()->fetchAll($sql, array($conditions['userId'], time() - 86400, $conditions['targetId'], $conditions['targetType']));
        } else {
            return $this->search($conditions, array('id' => 'DESC'), $offset, $limit);
        }
    }

    public function countH5MpsBatches($conditions)
    {
        if (isset($conditions['userId'])) {
            $sql = "
                SELECT COUNT(DISTINCT(coupon_batch.id))
                FROM coupon_batch coupon_batch
                    LEFT JOIN (
                        SELECT userId, batchId, status
                        FROM coupon
                        WHERE userId = ?
                    ) coupon
                    ON coupon_batch.id = coupon.batchId
                WHERE (coupon_batch.h5MpsEnable = 1)
                    AND ((coupon.userId IS NULL AND coupon_batch.unreceivedNum > 0) OR (coupon.status = 'receive'))
                    AND (coupon_batch.deadline > ?)
                    AND (coupon_batch.targetId IN (0, ?))
                    AND (coupon_batch.targetType IN ('all', ?))
            ";

            return $this->db()->fetchColumn($sql, array($conditions['userId'], time() - 86400, $conditions['targetId'], $conditions['targetType']));
        } else {
            return $this->count($conditions);
        }
    }

    public function findBatchByPrefix($prefix)
    {
        return $this->findByFields(array(
            'prefix' => $prefix,
        ));
    }
}
