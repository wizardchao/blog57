<?php
    /**
     * Created by PhpStorm.
     * User: Chaos
     * Date: 2018/6/15
     * Time: 17:10
     */

    class MessageService
    {
        public function __construct()
        {
            $this->messageModel = new MessageModel();
        }


        /*
         * 发送短信验证码
         */
        public function send_code($mobile, $codeNum)
        {

        }
    }