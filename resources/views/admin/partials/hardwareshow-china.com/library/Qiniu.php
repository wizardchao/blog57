<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2018/7/10
 * Time: 11:17
 */
require('Qiniu/autoload.php');
use Qiniu\Auth as Auth;
use Qiniu\Storage\BucketManager;
use Qiniu\Storage\UploadManager;

class Qiniu{

    public  static $request;
    public  static $set;
    private static $accessKey;
    private static $secretKey;
    private static $domain;
    private static $bucket;
    public  $file=array();
    public  $filename='';

    public function __construct()
    {
        self::$set=new WebSetService();
        $qiniu=self::$set->getWebSettingByCache('qiniu');
        self::$accessKey=$qiniu['accessKey'];
        self::$secretKey=$qiniu['secretKey'];
        self::$domain=$qiniu['domain'];
        self::$bucket=$qiniu['bucket'];

    }


    /**
     * @param $filename
     * @return string
     * @throws Exception
     *接收文件上传的file变量的name值
     */

    /**
     * @param $filename
     * @return $this
     * 获取文件对象
     */
    public function file($filename){
        $this->filename=$filename;
        if ($_FILES[$filename]["error"] > 0)
        {
            echo "Error: " . $_FILES["file"]["error"] . "<br />";
        }
        else{
            $this->file=$_FILES;
            return $this->file;
        }
    }


    /**
     * @return mixed
     * 得到文件的未保存路径
     */
    public function getFileRealPath(){
        $file=$this->file;
        return $file[$this->filename]['tmp_name'];

    }


    /**
     * @param $store_path
     * @return bool
     * 存储文件
     */
    public function moveFile($store_path){
        $res=move_uploaded_file($this->file[$this->filename]['tmp_name'],$store_path);
        return $res;
    }

    public function  getFileExt(){
        $file=$this->file[$this->filename]['name'];
        $ext = pathinfo($file);
        $ext =$ext['extension'];
        return $ext;
    }


    public function  upload($filename){
        $this->file($filename);

// 要上传图片的本地路径
        $filePath = $this->getFileRealPath();
        $ext=$this->getFileExt();
        $key =date('YmdHis') . rand(0, 999999) . '.' . $ext;
// 需要填写你的 Access Key 和 Secret Key
        $accessKey = self::$accessKey;
        $secretKey = self::$secretKey;
// 构建鉴权对象
        $auth = new Auth($accessKey, $secretKey);
// 要上传的空间
        $bucket = self::$bucket;
        $domain = self::$domain;
        $token = $auth->uploadToken($bucket);
// 初始化 UploadManager 对象并进行文件的上传
        $uploadMgr = new UploadManager();
// 调用 UploadManager 的 putFile 方法进行文件的上传
        list($ret, $err) = $uploadMgr->putFile($token, $key, $filePath);
        if ($err !== null) {
            echo array("err"=>1,"msg"=>$err,"data"=>"");exit;
        } else {
            //返回图片的完整URL
               $res= $domain.'/'.$ret['key'];
            return (new UtilsHelper())->addHttp($res);
        }

    }
}