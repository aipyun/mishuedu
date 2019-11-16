<?php

namespace VipPlugin\Api\Resource\VipUser;

use ApiBundle\Api\Resource\Filter;

class VipUserFilter extends Filter
{
    protected $simpleFields = array(
        'levelId', 'vipName', 'deadline', 'seq', 'level'
    );

    protected function simpleFields(&$data)
    {
        $level = $data['level'];

        $data['levelId'] = $level['id'];
        $data['vipName'] = $level['name'];
        $data['seq'] = $level['seq'];
        $data['deadline'] = date('c', $data['deadline']);
        unset($data['level']);
    }

}