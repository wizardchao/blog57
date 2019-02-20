<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2018/7/9
 * Time: 17:43
 */
class BaiduMap{
    public static $ak;
    public static $set;
    public static $util;


    public function __construct()
    {
        self::$set=new WebSetService();
        $baidu=self::$set->getWebSettingByCache('baidumap');
        //$baidu=json_decode($baidu,true);
        self::$ak=$baidu['baidu_ak'];
        self::$util=new UtilsHelper();

    }


    /**
     * @param $add_name
     * @return mixed
     * 通过地址获取经纬度
     */
    public static function getLngLatByAddressName($add_name){
        $lnglat=self::$util->curl_get('http://api.map.baidu.com/geocoder/v2/?address='.$add_name.'&output=json&ak='.self::$ak);
        return $lnglat;
    }


    /**
     * @param $ip
     * @return mixed
     * 通过ip地址获取地址信息
     */
    public static function getAddressByIp($ip){
        $addr=self::$util->curl_get('http://api.map.baidu.com/location/ip?ip'.$ip.'&coor=bd09ll&ak='.self::$ak);
        return self::$util->decodeUnicode($addr);
    }


}