<?php

namespace VipPlugin;

use Codeages\Biz\Framework\Context\Biz;
use VipPlugin\Biz\Importer\VipImporter;
use Codeages\PluginBundle\System\PluginBase;
use VipPlugin\Biz\Vip\Accessor\JoinClassroomVipAccessor;
use VipPlugin\Biz\Vip\Accessor\JoinCourseVipAccessor;
use VipPlugin\Biz\Vip\Accessor\LearnCourseVipAccessor;
use VipPlugin\Biz\Vip\Accessor\LearnClassroomVipAccessor;
use VipPlugin\Biz\OrderFacade\Product\VipProduct;
use Biz\Accessor\AccessorChain;
use VipPlugin\Biz\Vip\Accessor\JoinVipAccessor;

class VipPlugin extends PluginBase
{
    public function boot()
    {
        parent::boot();
        $biz = $this->container->get('biz');
        $biz['importer.vip'] = function ($biz) {
            return new VipImporter($biz);
        };

        $biz[sprintf('order.product.%s', VipProduct::TYPE)] = $biz->factory(function ($biz) {
            $vipProduct = new VipProduct();
            $vipProduct->setBiz($biz);
            return $vipProduct;
        });

        $this->registerAccessors($biz);
    }

    public function registerAccessors(Biz $biz)
    {
        $biz->extend('course.learn_chain', function (AccessorChain $chain, $biz) {
            $chain->add(new LearnCourseVipAccessor($biz), 10);
            return $chain;
        });

        $biz->extend('classroom.learn_chain', function (AccessorChain $chain, $biz) {
            $chain->add(new LearnClassroomVipAccessor($biz), 10);
            return $chain;
        });

        $biz->extend('course.join_chain', function (AccessorChain $chain, $biz) {
            $jcAccessor = $chain->getAccessor('JoinCourseAccessor');
            $jcAccessor->setNextAccessor(new JoinCourseVipAccessor($biz));
            return $chain;
        });

        $biz->extend('classroom.join_chain', function (AccessorChain $chain, $biz) {
            $jcAccessor = $chain->getAccessor('JoinClassroomAccessor');
            $jcAccessor->setNextAccessor(new JoinClassroomVipAccessor($biz));
            return $chain;
        });
    }
}
