<?php
    /**
     * Created by PhpStorm.
     * User: Chaos
     * Date: 2018/6/25
     * Time: 17:51
     */
    require APPLICATION_PATH . '/modules/api/controllers/CommonController.php';

    class AboutController extends CommonController
    {
        public function init()
        {
            parent::init();
            $this->aboutService = new AboutService();
        }


        /**
         * 栏目详情
         */
        public function about_detailAction()
        {
            $request  = $this->getRequest();
            $about_id = (int)$request->getParam('id');
            if (empty($about_id)) {
                return $this->showJson(201, '编号不能为空！');
            }

            $about_info = $this->aboutService->getAboutInfoById($about_id);
            if (empty($about_info)) {
                return $this->showJson(202, '栏目信息为空！');
            }

            $data = array(
                'id' => $about_id,
                'title' => $about_info['about_title'],
                'content' => htmlspecialchars_decode($about_info['about_content']),
                'keywords' => $about_info['page_keywords'],
                'desc' => $about_info['page_description'],
            );

            return $this->showJson(200, $data);
        }


        public function minusAction()
        {
            $orderService = new OrderService();
            $order_no='1810171355471111';
            $re           = $orderService->MinusStockByOrder($order_no);
            if ($re) return $this->showJson(200);
        }
    }