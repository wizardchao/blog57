<?php
    /**
     * Created by PhpStorm.
     * User: Chaos
     * Date: 2018/8/15
     * Time: 13:55
     */

    require APPLICATION_PATH . '/modules/manage/controllers/CommonController.php';

    class SubscriptionController extends CommonController
    {


        public function init()
        {
            parent::init();
            $this->page_size           = 20;
            $this->subscriptionService = new SubscriptionService();
        }


        public function subscription_listAction()
        {
            $request           = $this->getRequest();
            $page              = (int)$request->getParam('page');
            $subscription_email=$request->getParam('subscription_email');
            $subscription_name=$request->getParam('subscription_name');
            $param             = compact('subscription_email','subscription_name');
            $subscription_info = $this->subscriptionService->getSubscriptionInfoByPage($page, $this->page_size, $param);
            $sub_list          = $subscription_info['list'];
            $page_info         = $subscription_info['page'];
            $this->view->assign(
                array(
                    'sub_list' => $sub_list,
                    'param' => $param,
                    'page' => $page_info,
                ));
            $this->render('subscription_list');
        }


        public function subscription_delAction()
        {
            $request = $this->getRequest();
            $id      = (int)$request->getParam('id');
            if (empty($id)) {
                return $this->showWarning('对不起，参数不能为空。');
            }

            $re = $this->subscriptionService->delSubscription($id);
            if ($re) {
                return $this->showMessage('恭喜您，删除订阅记录成功。', '/subscription/subscription_list');
            } else {
                return $this->showWarning('对不起，删除订阅记录失败。');
            }
        }


    }