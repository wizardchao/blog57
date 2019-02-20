<?php
/**
 * Created by PhpStorm.
 * User: Chris_Chiang
 * Date: 2018/6/29
 * Time: 12:55
 */
    class WebSetService
    {
        public $WebSetModel;


        /*
        * 构造函数
        */
        public function __construct()
        {

            $this->utilHelpers  = new UtilsHelper();
            $this->WebSetModel  = new WebSetModel();
        }


        /**
         * @param $posts
         * @return bool|int|type
         * 保存系统信息
         */
        public function saveSyetemInfo($posts)
        {
            if($posts['set_info']=='base'){
                $res=$this->saveSingleSetting($posts);
            }
            if($posts['set_info']=='secure'){

                $res=$this->saveSingleSetting($posts);
            }
            if($posts['set_info']=='email'){
                $res=$this->saveSingleSetting($posts);

            }
            if($posts['set_info']=='baidumap'){

                $res=$this->saveSingleSetting($posts);

            }
            if($posts['set_info']=='qiniu'){
                $res=$this->saveSingleSetting($posts);

            }
            //print_r($res);exit;
            if($res){
                return $res;
            }else{
                return false;
            }


        }


        /**
         * @param $data
         * @return type
         * 保存单个配置信息
         */
        public function saveSingleSetting($data){
           //print_r($data);exit;
            $set_info=$data['set_info'];
            unset($data['set_info']);
            $info=$this->WebSetModel->getSetInfoByName($set_info);
            if(empty($info)){
                foreach($data as $key=>$value){
                    $set=array();
                    $set['set_key']=$key;
                    $set['set_value']=$value;
                    $set['set_info']=$set_info;
                    $set['time_update']= time();
                    $this->WebSetModel->insert($set);
                }
            }else{
                foreach($info as $k){
                    foreach($data as $key=>$value){
                        $set=array();
                        if($k['set_key']==$key){
                            $id['id']=$k['id'];
                            $set['set_key']=$key;
                            $set['set_value']=$value;
                            $set['set_info']=$set_info;
                            $set['time_update']= time();
                            $this->WebSetModel->update($id,$set);
                        }

                    }
                }


          }
            $info=$this->WebSetModel->getSetInfoByName($set_info);
            $info=$this->recombineInfo($info);

           return $info;
        }

        /**
         *重组数据
         * @param $info
         * @return
         */
        public function recombineInfo($info){
            $newinfo=array();
            foreach($info as $in){
                $newinfo[$in['set_key']]=$in['set_value'];

            }
            return $newinfo;
        }
        /**
         * @param $gets
         * @return string
         * 获取用户信息配置，通过传过来的参数，得到数据表配置信息，返回json格式数据。
         */
        public function getSyetemInfo($gets){
            if(isset($gets)){
                $info=$this->WebSetModel->getSetInfoByName($gets);
                $info=$this->recombineInfo($info);
                if(empty($info)){

                    return '';
                }else{
                    return $info;
                }
            }else{
                return '';
            }

        }

        /**
         * @param $gets
         * @return string|type
         * 从缓存里面得到网站设置信息
         */
        public function getWebSettingByCache($gets){
            $info=Star_Cache::get($gets);
            if(!$info){
                $info=$this->getSyetemInfo($gets);
                Star_Cache::set($gets,$info,100);
                $info=Star_Cache::get($gets);
                return $info;
            }else{
                return $info;
            }
        }

      

    }