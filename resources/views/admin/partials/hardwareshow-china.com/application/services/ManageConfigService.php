<?php
    /**
     * Created by PhpStorm.
     * User: Chaos
     * Date: 2018/8/15
     * Time: 0:37
     */

    class ManageConfigService
    {
        private $manageConfigModel;

        /*
         * 构造函数
         */
        public function __construct()
        {
            $this->manageConfigModel = new ManageConfigModel();
        }


        public function getConfigInfo($config_id=1)
        {
            $info=$this->manageConfigModel->getConfigInfo($config_id);
            $info['home_ad_img']=DOMAIN_IMG.$info['home_ad_img'];
            $info['logo']=DOMAIN_IMG.$info['logo'];
            return $info;
        }


        public function editConfigInfo($id, $param)
        {
            $where=array(
                'config_id' => $id,
            );

            return $this->manageConfigModel->update($where,$param);
        }


        public function getHomedesc($config_id){
            $home_info=$this->getConfigInfo($config_id);
            return htmlspecialchars_decode($home_info['home_desc']);
        }


        public function getConfig($config_id=1){
            return $this->manageConfigModel->getConfigInfo($config_id);
        }

    }
