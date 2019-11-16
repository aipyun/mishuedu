<?php

namespace VipPlugin\Controller;

use Symfony\Component\HttpFoundation\Request;
use AppBundle\Controller\Admin\BaseController;
use AppBundle\Common\Paginator;

class VipLevelAdminController extends BaseController
{
    public function indexAction(Request $request)
    {
        $conditions = array_filter($request->query->all());
        $paginator = new Paginator(
            $this->get('request'),
            $this->getLevelService()->searchLevelsCount($conditions),
            20
        );

        $memberlevels = $this->getLevelService()->searchLevels(
            $conditions,
            array('seq' => 'asc'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );
        foreach ($memberlevels as &$memberlevel) {
            $memberlevel['memberNum'] = $this->getVipService()->searchMembersCount(array('level' => $memberlevel['id']));
        }

        return $this->render('VipPlugin:VipLevelAdmin:index.html.twig', array(
            'memberlevels' => $memberlevels,
            'paginator' => $paginator,
        ));
    }

    public function createAction(Request $request)
    {
        $coinSetting = $this->getSettingService()->get('coin', array());

        $cashRate = isset($coinSetting['cash_rate']) ? $coinSetting['cash_rate'] : 1;

        if ('POST' == $request->getMethod()) {
            $conditions = $request->request->all();
            $memberLevel = $this->getLevelService()->createLevel($conditions);
            if ($memberLevel) {
                $this->setFlashMessage('success', '会员等级已保存！');
            }

            return $this->redirect($this->generateUrl('admin_vip_level'));
        }

        return $this->render('VipPlugin:VipLevelAdmin:memberlevel.html.twig', array(
         'cashRate' => $cashRate, ));
    }

    public function updateAction(Request $request, $id)
    {
        $memberLevel = $this->getLevelService()->getLevel($id);

        if (empty($memberLevel)) {
            throw $this->createNotFoundException();
        }

        $coinSetting = $this->getSettingService()->get('coin', array());
        $cashRate = isset($coinSetting['cash_rate']) ? $coinSetting['cash_rate'] : 1;

        if ('POST' == $request->getMethod()) {
            $conditions = $request->request->all();
            $memberLevel = $this->getLevelService()->updateLevel($id, $conditions);

            if ($memberLevel) {
                $this->setFlashMessage('success', '会员等级已更新！');
            }

            return $this->redirect($this->generateUrl('admin_vip_level'));
        }

        return $this->render('VipPlugin:VipLevelAdmin:memberlevel.html.twig', array(
            'memberlevel' => $memberLevel,
            'cashRate' => $cashRate,
        ));
    }

    public function deleteAction(Request $request, $id)
    {
        $this->getLevelService()->deleteLevel($id);

        return $this->createJsonResponse(true);
    }

    public function onAction(Request $request, $id)
    {
        $this->getLevelService()->onLevel($id);

        return $this->createJsonResponse(true);
    }

    public function offAction(Request $request, $id)
    {
        $this->getLevelService()->offLevel($id);

        return $this->createJsonResponse(true);
    }

    public function iconAction(Request $request)
    {
        return $this->render('VipPlugin:VipLevelAdmin:icon-modal.html.twig');
    }

    public function sortAction(Request $request)
    {
        $this->getLevelService()->sortLevels($request->request->get('ids'));

        return $this->createJsonResponse(true);
    }

    public function chooserAction(Request $request)
    {
        $conditions = array_filter($request->query->all());
        $paginator = new Paginator(
                    $this->get('request'),
                    $this->getLevelService()->searchLevelsCount($conditions),
                    20
            );
        $memberlevels = $this->getLevelService()->searchLevels(
                    $conditions,
                    array(),
                    $paginator->getOffsetCount(),
                    $paginator->getPerPageCount()
            );
        foreach ($memberlevels as &$memberlevel) {
            $memberlevel['memberNum'] = $this->getVipService()->searchMembersCount(array('level' => $memberlevel['id']));
        }

        return $this->render('VipPlugin:VipLevelAdmin:level-chooser.html.twig', array(
            'conditions' => $conditions,
            'memberlevels' => $memberlevels,
               'paginator' => $paginator,
        ));
    }

    protected function getVipService()
    {
        return $this->createService('VipPlugin:Vip:VipService');
    }

    protected function getLevelService()
    {
        return $this->createService('VipPlugin:Vip:LevelService');
    }

    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }
}
