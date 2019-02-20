<?php
    /**
     * Created by PhpStorm.
     * User: Chaos
     * Date: 2018/8/31
     * Time: 13:52
     */

    class CommonController extends Star_Controller_Action
    {
        protected $authService;
        protected $member_id;
        protected $utilsHelper;
        protected $lang;

        public function init()
        {
            $request             = $this->getRequest();
            $GLOBALS['language'] = $request->getParam('language');
            $this->authService   = new AuthService();
            $this->utilsHelper   = new UtilsHelper();
            $this->lang          = new LanguageService();
            $action              = $this->request->getActionName();
            $controller          = $this->request->getControllerName();
            $controller_arr      = array( 'order', 'shopcar' );
            //          $this->member_id     = 111;
            $this->member_id = ($this->authService->getMemberId());
            if (in_array($controller, $controller_arr) || ($controller == 'member' && $action != 'userinfo')) {
                $info = $this->ck_login();
                if ($info) {
                    $this->member_id = ($this->authService->getMemberId());
//                    $this->member_id = 111;
                }
            }
        }


        public function ck_login()
        {
            $info = $this->authService->getLoginInfo();
            if (empty($info)) {
                $this->showJson(300, '微信号尚未绑定');
                exit;
            }
            return $info;
        }

    }