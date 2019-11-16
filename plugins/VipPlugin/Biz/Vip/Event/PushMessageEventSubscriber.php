<?php

namespace VipPlugin\Biz\Vip\Event;

use Biz\CloudPlatform\QueueJob\PushJob;
use Biz\System\Service\SettingService;
use Biz\User\Service\UserService;
use Codeages\Biz\Framework\Event\Event;
use Codeages\Biz\Framework\Queue\Service\QueueService;
use Codeages\PluginBundle\Event\EventSubscriber;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use VipPlugin\Biz\Vip\Service\LevelService;

class PushMessageEventSubscriber extends EventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array(
            'admin.operate.vip_member' => 'onVipCreated',
            'vip.cancel' => 'onVipCancel',
        );
    }

    public function onVipCreated(Event $event)
    {
        $member = $event->getSubject();

        if (!$this->isManualOperation($member))
        {
            return ;
        }

        $user = $this->getUserService()->getUser($member['userId']);
        $operator = $this->getUserService()->getUser($member['operatorId']);
        $level = $this->getLevelService()->getLevel($member['levelId']);

        if ($this->isIMEnabled()) {
            $from = array(
                'type' => 'vip',
                'id' => $member['levelId'],
            );

            $to = array(
                'type' => 'user',
                'id' => $user['id'],
                'convNo' => $this->getConvNo(),
            );

            $body = array(
                'type' => 'vip.add',
                'title' => "{$level['name']}",
                'message' => "您被管理员添加为{$level['name']}",
                'operatorId' => $operator['id'],
            );

            $this->createPushJob($from, $to, $body);
        }
    }

    public function onVipCancel(Event $event)
    {
        $member = $event->getSubject();

        $user = $this->getUserService()->getUser($member['userId']);
        $operator = $this->getUserService()->getUser($member['operatorId']);
        $level = $this->getLevelService()->getLevel($member['levelId']);

        if ($this->isIMEnabled()) {
            $from = array(
                'type' => 'vip',
                'id' => $member['levelId'],
            );

            $to = array(
                'type' => 'user',
                'id' => $user['id'],
                'convNo' => $this->getConvNo(),
            );

            $body = array(
                'type' => 'vip.delete',
                'title' => "{$level['name']}",
                'message' => "您已经被取消{$level['name']}，如有疑问请联系管理员",
                'operatorId' => $operator['id'],
            );

            $this->createPushJob($from, $to, $body);
        }

    }

    private function isManualOperation($member)
    {
        if ($member['operatorId'] == 0) {
            return false;
        }
        if($member['operatorId'] == $member['userId']) {
            return false;
        }

        if ($member['boughtType'] != 'new') {
            return false;
        }

        return true;
    }

    private function getConvNo()
    {
        $imSetting = $this->getSettingService()->get('app_im', array());
        $convNo = isset($imSetting['convNo']) && !empty($imSetting['convNo']) ? $imSetting['convNo'] : '';

        return $convNo;
    }

    private function isIMEnabled()
    {
        $setting = $this->getSettingService()->get('app_im', array());

        if (empty($setting) || empty($setting['enabled'])) {
            return false;
        }

        return true;
    }

    private function createPushJob($from, $to, $body)
    {
        $pushJob = new PushJob(array(
            'from' => $from,
            'to' => $to,
            'body' => $body,
        ));

        $this->getQueueService()->pushJob($pushJob);
    }

    protected function createService($alias)
    {
        return $this->getBiz()->service($alias);
    }

    /**
     * @return QueueService
     */
    protected function getQueueService()
    {
        return $this->createService('Queue:QueueService');
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->createService('User:UserService');
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }

    /**
     * @return LevelService
     */
    protected function getLevelService()
    {
        return $this->createService('VipPlugin:Vip:LevelService');
    }
}