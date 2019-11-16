<?php

namespace VipPlugin\Controller;

use AppBundle\Controller\BaseController;
use Symfony\Component\HttpFoundation\Request;

class VipOrderController extends BaseController
{
    public function tryBuyAction(Request $request)
    {
        $paymentSetting = $this->getSettingService()->get('payment', array());
        if (!$paymentSetting['enabled']) {
            return $this->render('buy-flow/payments-disabled-modal.html.twig');
        } else {
            return $this->createJsonResponse(true);
        }
    }

    public function buyAction(Request $request)
    {
        if (!$this->setting('vip.enabled')) {
            return $this->createMessageResponse('info', '会员专区已关闭');
        }

        $currentUser = $this->getCurrentUser();

        if (!$currentUser->isLogin()) {
            return $this->redirect($this->generateUrl('login'));
        }

        $member = $this->getVipService()->getMemberByUserId($currentUser->id);
        if ($member) {
            return $this->redirect($this->generateUrl('vip_renew'));
        }

        $levels = $this->getLevelService()->findEnabledLevels();
        $selectedLevel = $request->query->get('level', 0);
        if (empty($selectedLevel) && !empty($levels)) {
            $selectedLevel = $levels[0]['id'];
        }

        $prices = $this->makeLevelPrices($levels);

        return $this->render('VipPlugin:VipOrder:buy.html.twig', array(
            'levels' => $this->makeLevelChoices($levels),
            'selectedLevel' => $selectedLevel,
            'prices' => $prices,
            'prices_json' => json_encode($prices),
            'payments' => $this->getEnabledPayments(),
            'isAdmin' => $currentUser->isAdmin(),
            'defaultBuyMonth' => $this->setting('vip.default_buy_months'),
            'defaultBuyYear' => $this->setting('vip.default_buy_years'),
            'buyType' => $this->setting('vip.buyType'),
        ));
    }

    public function renewAction(Request $request)
    {
        if (!$this->setting('vip.enabled')) {
            return $this->createMessageResponse('info', '会员专区已关闭');
        }

        $currentUser = $this->getCurrentUser();
        if (!$currentUser->isLogin()) {
            return $this->redirect($this->generateUrl('login'));
        }

        $member = $this->getVipService()->getMemberByUserId($currentUser->id);
        if (empty($member)) {
            return $this->redirect($this->generateUrl('vip_buy'));
        }

        $level = $this->getLevelService()->getLevel($member['levelId']);
        if (empty($level) or empty($level['enabled'])) {
            return $this->createMessageResponse('info', '该会员等级已经关闭，不能续费');
        }

        $prices = $this->makeLevelPrices(array($level));

        return $this->render('VipPlugin:VipOrder:renew.html.twig', array(
            'member' => $member,
            'level' => $level,
            'prices' => $prices,
            'prices_json' => json_encode($prices),
            'nowTime' => time(),
            'payments' => $this->getEnabledPayments(),
            'defaultBuyMonth' => $this->setting('vip.default_buy_months'),
            'defaultBuyYear' => $this->setting('vip.default_buy_years'),
            'buyType' => $this->setting('vip.buyType'),
        ));
    }

    public function upgradeAction(Request $request)
    {
        if (!$this->setting('vip.enabled')) {
            return $this->createMessageResponse('info', '会员专区已关闭');
        }

        $currentUser = $this->getCurrentUser();
        if (!$currentUser->isLogin()) {
            return $this->redirect($this->generateUrl('login'));
        }

        $coinSetting = $this->getSettingService()->get('coin', array());
        if (isset($coinSetting['cash_rate'])) {
            $cashRate = $coinSetting['cash_rate'];
        } else {
            $cashRate = 1;
        }

        $member = $this->getVipService()->getMemberByUserId($currentUser->id);
        if (empty($member)) {
            return $this->redirect($this->generateUrl('vip_buy'));
        }

        $level = $this->getLevelService()->getLevel($member['levelId']);
        if (empty($level)) {
            return $this->createMessageResponse('error', '该会员等级不存在，不能升级！');
        }

        $levels = $this->getLevelService()->findNextEnabledLevels($level['id']);
        if (empty($levels)) {
            return $this->createMessageResponse('info', '没有可升级的会员等级。');
        }

        $canUpgradeMember = $this->getVipService()->canUpgradeMember($currentUser['id']);

        return $this->render('VipPlugin:VipOrder:upgrade.html.twig', array(
            'member' => $member,
            'level' => $level,
            'prices' => $this->makeLevelPrices(array($level)),
            'levels' => $levels,
            'payments' => $this->getEnabledPayments(),
            'cashRate' => $cashRate,
            'canUpgradeMember' => $canUpgradeMember,
        ));
    }

    public function upgradeAmountAction(Request $request)
    {
        $currentUser = $this->getCurrentUser();
        if (!$currentUser->isLogin()) {
            throw $this->createAccessDeniedException();
        }

        $newLevelId = $request->query->get('levelId');

        $amount = $this->getVipService()->calUpgradeMemberAmount($currentUser->id, $newLevelId);

        return $this->createJsonResponse($amount);
    }

    private function makeLevelPrices($levels)
    {
        $prices = array();
        foreach ($levels as $level) {
            $prices[$level['id']] = array();
            $prices[$level['id']]['month'] = (float) $level['monthPrice'];
            $prices[$level['id']]['year'] = (float) $level['yearPrice'];
        }

        return $prices;
    }

    private function makeLevelChoices($levels)
    {
        $choices = array();
        foreach ($levels as $level) {
            $choices[$level['id']] = $level['name'];
        }

        return $choices;
    }

    private function getEnabledPayments()
    {
        $enableds = array();

        $setting = $this->setting('payment', array());

        if (empty($setting['enabled'])) {
            return $enableds;
        }

        $payNames = array('alipay');
        foreach ($payNames as $payName) {
            if (!empty($setting[$payName.'_enabled'])) {
                $enableds[$payName] = array(
                    'type' => empty($setting[$payName.'_type']) ? '' : $setting[$payName.'_type'],
                );
            }
        }

        return $enableds;
    }

    public function getVipService()
    {
        return $this->createService('VipPlugin:Vip:VipService');
    }

    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }

    public function getLevelService()
    {
        return $this->createService('VipPlugin:Vip:LevelService');
    }
}
