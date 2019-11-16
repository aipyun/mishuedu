<?php

namespace Tests\Vip;

use Biz\BaseTestCase;

class VipServiceTest extends BaseTestCase
{
    public function testGetMemberByUserId()
    {
        $fakeVip = array(
            'id' => 1,
            'levelId' => 1,
            'deadline' => time(),
            'boughtUnit' => 'year',
        );
        $this->mockBiz('VipPlugin:Vip:VipDao', array(
            array(
                'functionName' => 'getMemberByUserId',
                'returnValue' => $fakeVip)
        ));

        $result = $this->getVipService()->getMemberByUserId(1);

        $this->assertEquals($fakeVip, $result);
    }

    private function getVipService()
    {
        return $this->createService('VipPlugin:Vip:VipService');
    }
}
