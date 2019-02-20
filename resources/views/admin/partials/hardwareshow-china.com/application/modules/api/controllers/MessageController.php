<?php
    /**
     * Created by PhpStorm.
     * User: Chaos
     * Date: 2018/6/15
     * Time: 16:17
     */

    class MessageController extends Star_Controller_Action
    {
        public function init()
        {
            $this->utilsHelper       = new UtilsHelper();
            $this->verifyCodeService = new VerifyCodeService();
            $this->messageService    = new MessageService();
        }

        public function codeAction()
        {
            $request = $this->getRequest();
            $mobile  = $request->getParam('mobile');
            if (empty($mobile)) {
                return $this->showJson(110, "手机号不能为空！");
            }
            //判断号码格式是否正确
            if (!preg_match("/^1[345678]{1}\d{9}$/", $mobile)) {
                return $this->showJson(111, "手机号格式有误");
            }

            //获取随机数作为验证码
            $rand_num = $this->utilsHelper->get_rand_number(1, 9, 6);
            $content = "您的验证码为：{$rand_num}，请于1分钟内填写，请勿泄露。";

            //短信接口
            $data = $this->messageService->sendCodeTencent($mobile, $content, MESSAGEURL, MESS_APPID, MESS_APPKEY);
            if ($data['status'] == 200) {
                $status = 1;
                $re     = $this->verifyCodeService->setUserVerifyCode($mobile, $rand_num, $status);
                if ($re) {
                    //测试环境
                    $data = array(
                        'code' => (int)$rand_num,
                    );
                    return $this->showJson(200, $data);
                }
            }

            return $this->showJson($data['status'], $data['message']);
        }


    }