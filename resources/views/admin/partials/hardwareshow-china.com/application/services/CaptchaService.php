<?php
/**
 * Created by PhpStorm.
 * User: Chris_Chiang
 * Date: 2018/7/30
 * Time: 11:40
 */
class CaptchaService{


    public $sess;
    public $set;
    public function __construct()
   {
       if(!isset($_SESSION)){
           session_start();
       }
       $this->sess=session_id();
   }


    /**
     * 设置验证码图片
     * @param int $expire
     */
   public function set($expire=60)
   {
       $this->set =(new Captcha());
       $img=$this->set->doImg();
       $res=$this->set->getCode();
       Star_Cache::set($this->sess.'_captcha',$res,$expire);
       return $img;
   }


    /**
     * 获取验证码
     * @return type
     */
   public function get()
   {
       $code=Star_Cache::get($this->sess.'_captcha');
       return $code;
   }
}
