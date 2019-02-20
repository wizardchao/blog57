<?php
    /**
     * Created by PhpStorm.
     * User: Chaos
     * Date: 2018/8/16
     * Time: 16:04
     */
    require APPLICATION_PATH . '/modules/manage/controllers/CommonController.php';

    class ConfigController extends CommonController
    {
        protected $manageConfigService;
        private $config_id;
        private $config_info;

        public function init()
        {
            parent::init();
            $this->manageConfigService = new ManageConfigService();
            $this->config_id           = 1;

            $request = $this->getRequest();
            if ($request->isPost()) {
                $param = $request->getParams();
                $arr=array(
                    'company','header_desc','partner','contact','contact_way','secret','f_buyer_desc','s_buyer_desc','order_desc'
                );
                foreach ($param as $key => &$el){
                    if(in_array($key,$arr)){
                        $el=stripslashes($el);
                    }
                }
                $param['time_update'] = time();
                $re                   = $this->manageConfigService->editConfigInfo($this->config_id, $param);
                if (isset($re)) {
                    echo "<script>alert('恭喜您，修改配置成功!');history.back();</script>";
                } else {
                    return $this->showWarning('很遗憾，修改配置失败。');
                }
                exit;
            }
            $this->config_info = $this->manageConfigService->getConfig($this->config_id);
            $this->view->assign(
                array(
                    'param' => $this->config_info,
                )
            );
        }

        /**
         * 基本设置
         */
        public function basicAction()
        {
            $this->render('basic');
        }


        /**
         * 顶部设置
         */
        public function header_configAction()
        {
            $this->render('header_config');
        }


        /**
         * 底部设置
         */
        public function footer_configAction()
        {
            $this->render('footer_config');
        }


        /**
         * 内页设置
         */
        public function page_configAction()
        {
            $this->render('page_config');
        }


        /**
         * SEO设置
         */
        public function seo_configAction()
        {
            $this->render('seo_config');
        }
    }
