<?php
    /**
     * Created by PhpStorm.
     * User: Chaos
     * Date: 2018/3/1
     * Time: 17:53
     */

    class VerifyCodeService
    {
        const USER_VERIFY_CODE_KEY = 'user_verify_code_';

        /*
      * 构造函数
       */
        public function __construct()
        {
            $this->messCodeModel = new MessageCodeModel();
        }


        /*
        * 设置验证码缓存，同时存入数据库
        */
        public function setUserVerifyCode($mobile, $code, $status)
        {

            $key = self::USER_VERIFY_CODE_KEY . $mobile;
            Star_Cache::set($key, $code, 300);
            $verify_code_data = array(
                'account' => $mobile,
                'code' => $code,
                'time_create' => time(),
                'status' => $status,
            );

            $verify_code_id = $this->messCodeModel->insert($verify_code_data);
            return $verify_code_id;
        }


        /*
        * 验证手机号，验证码是否一致
        */
        public function checkVerifyCode($mobile, $code)
        {
            $key = self::USER_VERIFY_CODE_KEY . $mobile;
            if (Star_Cache::get($key) == $code) {
                return true;
            } else {
                return false;
            }
        }
    }