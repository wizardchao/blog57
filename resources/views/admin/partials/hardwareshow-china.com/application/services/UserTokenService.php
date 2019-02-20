<?php

/**
 * Created by PhpStorm.
 * User: Chris_Chiang
 * Date: 2018/7/30
 * Time: 10:36
 */

/**
 * 微信登录
 *小程序登陆逻辑
 */
class UserTokenService
{
    protected $code;
    protected $wxLoginUrl;
    protected $wxAppID;
    protected $wxAppSecret;
    protected $utils;
    protected $user;

    function __construct($code)
    {
        $this->utils=new UtilsHelper();
        $this->user=new MemberService();
        $this->code = $code;
        $this->wxAppID = APPID;
        $this->wxAppSecret = APPSECRET;
        $this->wxLoginUrl = sprintf(
            WX_LOGIN_URL, $this->wxAppID, $this->wxAppSecret, $this->code);
    }

    
    /**
     * 登陆
     * 思路1：每次调用登录接口都去微信刷新一次session_key，生成新的Token，不删除久的Token
     * 思路2：检查Token有没有过期，没有过期则直接返回当前Token
     * 思路3：重新去微信刷新session_key并删除当前Token，返回新的Token
     */
    public function get()
    {

        $result = $this->utils->curl_get($this->wxLoginUrl);
        $wxResult = json_decode($result, true);
        if (empty($wxResult)) {
           return array('error'=>'获取session_key及openID时异常，微信内部错误');
        }
        else {
            $loginFail = array_key_exists('errcode', $wxResult);
            if ($loginFail) {
                $this->processLoginError($wxResult);
            }
            else {
                return $this->grantToken($wxResult);
            }
        }
    }

    /**
     * 微信返回异常处理，默认不处理
     * @param $wxResult
     * @return array
     */
    private function processLoginError($wxResult)
    {

        return  array(
                'msg' => $wxResult['errmsg'],
                'error' => $wxResult['errcode']
            );
    }

    /**
     * 生成token
     * @return string
     */
    public static function generateToken()
    {
        $randChar = (new UtilsHelper())->getRandChar(32);
        $timestamp = $_SERVER['REQUEST_TIME_FLOAT'];
        $tokenSalt = TOKEN_SALT;
        return md5($randChar . $timestamp . $tokenSalt);
    }

    /**
     * 将微信返回信息，open_id存入缓存
     * @param $wxResult
     * @return array|string
     */
    private function saveToCache($wxResult)
    {
        $key = self::generateToken();
        $value = json_encode($wxResult);
        $expire_in = TOKEN_EXPIRE;
        $result = Star_Cache::set($key, $value, $expire_in);

        if (!$result){
            return array(
                'msg' => '服务器缓存异常',
                'error' => 10005
            );
        }
        return $key;
    }


    /**
     * 根据openid获取用户uid并且生成缓存的token，生成token权限
     * @param $wxResult
     * @return array|string
     */
    private function grantToken($wxResult)
    {

        $openid = $wxResult['openid'];
        $user = $this->user->getMemberInfoByOpenid($openid);
        if (!$user)
        {
            $uid = $this->newUser($openid);
        }
        else {
            $uid = $user['wx_open_id'];
        }
        $cachedValue = $this->prepareCachedValue($wxResult, $uid);
        $token = $this->saveToCache($cachedValue);
        return $token;
    }

    /**
     * 预处理缓存，缓存中存放用用户的uid和访问权限scope信息
     * @param $wxResult
     * @param $uid
     * @return mixed
     */
    private function prepareCachedValue($wxResult, $uid)
    {
        $cachedValue = $wxResult;
        $cachedValue['uid'] = $uid;
        //作用域权限处理，默认没有处理，后期根据业务逻辑需要进行处理
        $cachedValue['scope'] = 1;
        return $cachedValue;
    }

    /**
     * 插入新的用户，返回uid
     * @param $openid
     * @return type
     */
    private function newUser($openid)
    {
        $user_info=array('open_id'=>$openid,);
        $uid=$this->user->saveMemberFromWx($user_info);
        return $uid;
    }
}
