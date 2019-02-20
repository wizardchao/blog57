<?php
    /**
     * Created by PhpStorm.
     * User: Chaos
     * Date: 2018/8/15
     * Time: 12:29
     */

    class SubscriptionService
    {
        protected $subscriptionModel;

        public function __construct()
        {
            $this->subscriptionModel = new SubscriptionModel();
        }


        public function saveDescriptionRecord($param)
        {
            return $this->subscriptionModel->insert($param);
        }


        public function sendEmail($description_email)
        {
            return 1;
        }


        public function getCountByEmail($email)
        {
            $re = $this->subscriptionModel->getCountByEmail($email);
            return $re;
        }


        public function getSubscriptionInfoByPage($page, $page_size, $param)
        {
            $total     = $this->subscriptionModel->getAllCounts($param);
            $page      = Star_Page::setPage($page, $page_size, $total);
            $list      = $this->subscriptionModel->getSubscriptionInfoByPage($page, $page_size, $param);
            $page_info = compact('page', 'page_size', 'total');
            $page_data = Star_Page::show($page_info);
            return array( 'page' => $page_data, 'total' => $total, 'list' => $list );
        }


        public function delSubscription($id){
            $where=array(
                'subscription_id' => $id,
            );
            $param=array(
                'status' => -1,
            );
            return  $this->subscriptionModel->update($where,$param);
        }
    }