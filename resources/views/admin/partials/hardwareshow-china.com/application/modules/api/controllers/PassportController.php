<?php
    /**
     * Created by PhpStorm.
     * User: Chaos
     * Date: 2018/7/2
     * Time: 15:18
     */
    require APPLICATION_PATH . '/modules/api/controllers/CommonController.php';

    class PassportController extends CommonController
    {
        public function init()
        {
            parent::init();
            $this->utilsHelper       = new UtilsHelper();
            $this->verifyCodeService = new VerifyCodeService();
            $this->memberService     = new MemberService();
        }


        /**
         * 注册（身份验证）& 换绑
         */
        public function bindAction()
        {
            $authService = new AuthService();
            $request = $this->getRequest();
            $mobile  = $request->getParam('mobile');
            $code    = (int)$request->getParam('code');
                        $member_id = $this->member_id;
//            $member_id = $authService->getMemberId();
            if (empty($mobile)) {
                return $this->showJson(111, "手机号不能为空");
            }

            if (empty($code)) {
                return $this->showJson(114, "验证码不能为空");
            }

            //判断号码格式是否正确
            if (!preg_match("/^1[345678]{1}\d{9}$/", $mobile)) {
                return $this->showJson(121, "手机号格式有误");
            }

            if (empty($member_id)) {
                return $this->showJson(300, "微信号尚未绑定");
            }

            $member_info = $this->memberService->getMemberInfo($member_id);
            if (empty($member_info)) {
                return $this->showJson(300, '微信号尚未绑定！');
            }

            if (empty($member_info['mobile'])) {
                $ck_mobile_info = $this->memberService->ck_mobile($mobile);
                if ($ck_mobile_info) {
                    return $this->showJson(116, "手机号已重复！");
                }
            } else {
                $ck_mobile_info = $this->memberService->ck_mobile($mobile, $member_id);
                if ($ck_mobile_info) {
                    return $this->showJson(116, "手机号已重复！");
                }
            }

            if (!$this->verifyCodeService->checkVerifyCode($mobile, $code)) {
                return $this->showJson(115, "验证码错误！");
            }

            //判断手机号码唯一性

            $param = array(
                'mobile' => $mobile,
                'time_reg' => time(),
                'ip_reg' => $_SERVER["REMOTE_ADDR"],
            );

            $re = $this->memberService->editMember($member_id, $param);
            if (isset($re)) {
                return $this->showJson(200);
            }
        }


        /**
         * 换绑
         */
        public function bind1Action()
        {
            $request   = $this->getRequest();
            $mobile    = $request->getParam('mobile');
            $code      = (int)$request->getParam('code');
            $member_id = $this->member_id;

            if (empty($mobile)) {
                return $this->showJson(111, "手机号不能为空");
            }

            if (empty($code)) {
                return $this->showJson(114, "验证码不能为空");
            }

            //判断号码格式是否正确
            if (!preg_match("/^1[345678]{1}\d{9}$/", $mobile)) {
                return $this->showJson(121, "手机号格式有误");
            }

            if (empty($member_id)) {
                return $this->showJson(300, "微信号尚未绑定");
            }

            $member_info = $this->memberService->getMemberInfo($member_id);
            if (empty($member_info)) {
                return $this->showJson(300, '微信号尚未绑定');
            }

            if (!$member_info['mobile']) {
                return $this->showJson(301, '您未注册！');
            }

            if (!$this->verifyCodeService->checkVerifyCode($mobile, $code)) {
                return $this->showJson(115, "验证码错误！");
            }

            //判断手机号码唯一性
            $ck_mobile_info = $this->memberService->ck_mobile($mobile, $member_id);
            if ($ck_mobile_info) {
                return $this->showJson(116, "手机号已重复！");
            }
            $param = array(
                'mobile' => $mobile,
                'time_bind' => time(),
                'ip_bind' => $_SERVER["REMOTE_ADDR"],
            );

            $re = $this->memberService->editMember($member_id, $param);
            if (isset($re)) {
                return $this->showJson(200);
            }

            return $this->showJson(222, '换绑未成功！');
        }

    }