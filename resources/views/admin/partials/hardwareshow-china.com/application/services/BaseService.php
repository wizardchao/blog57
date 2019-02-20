<?php
    /**
     * Created by PhpStorm.
     * User: Chaos
     * Date: 2018/8/14
     * Time: 13:56
     */

    class BaseService
    {
        protected $aboutModel;
        protected $newsModel;
        protected $newsCategoryModel;
        protected $dataModel;
        protected $globalFoodModel;
        protected $linkModel;
        protected $manageConfigService;
        protected $dd;
        protected $adService;
        protected $adId=17;

        /*
         * 构造函数
         */

        public function __construct()
        {
            $this->aboutModel        = new AboutModel();
            $this->newsModel         = new NewsModel();
            $this->newsCategoryModel = new NewsCategoryModel();
            $this->globalFoodModel   = new GlobalFoodModel();
            $this->dataModel         = new DataModel();
            $this->linkModel         = new LinkModel();
            $this->manageConfigService = new ManageConfigService();
            $this->dd=new Dd();
            $this->adService=new AdService();
        }


        public function getHeaderData()
        {
            $is_show=1;
            $first_nav_list = $this->aboutModel->getAboutParent($is_show);
            foreach ($first_nav_list as &$list) {
                $list['child'] = $this->aboutModel->getAboutChildren($list['about_id'], $is_show);
            }
            return $first_nav_list;
        }


        public function getActivity()
        {
        }


        public function getNews()
        {
            $news_cate_info = $this->newsCategoryModel->getAllCategory();
            foreach ($news_cate_info as &$val) {
                $val['child'] = $this->newsModel->getNewsInfoByCate($val['category_id']);
            }

            return $news_cate_info;
        }


        public function getDataInfo()
        {
            $page      = 0;
            $page_size = 5;
            $param     = array();
            $data_info = $this->dataModel->getDataInfoByPage($page, $page_size, $param);
            return $data_info;
        }


        public function getGlobalFoodInfo()
        {
            $page      = 0;
            $page_size = 5;
            $param     = array();
            $data_info = $this->globalFoodModel->getGlobalFoodInfoByPage($page, $page_size, $param);
            return $data_info;
        }


        public function getLinkInfo()
        {
            $cate_id=1001;
            $pic_link_info=$this->linkModel->getLinkByCate($cate_id);
            foreach ($pic_link_info as &$info) {
                $info['link_img']=DOMAIN_IMG.$info['link_img'];
            }
            return $pic_link_info;
        }


        public function getWordLinkInfo()
        {
            $cate_id=1002;
            $pic_link_info=$this->linkModel->getLinkByCate($cate_id);
            foreach ($pic_link_info as &$info) {
                $info['link_img']=DOMAIN_IMG.$info['link_img'];
            }
            return $pic_link_info;
        }


        public function getConfig()
        {
            $info=$this->manageConfigService->getConfigInfo();
            $info['logo']=$this->dd->del_img($info['logo']);
            $info['black_logo']=$this->dd->del_img($info['black_logo']);
            $info['home_title']=$info['home_title']?:$info['common_title'];
            $info['home_keywords']=$info['home_keywords']?:$info['common_keywords'];
            $info['home_description']=$info['home_description']?:$info['common_description'];
            $info['home_desc']=$this->dd->deal_desc($info['home_desc']);
            // $info['home_middle_ad']=$this->getAdInfo($this->adId);
            return $info;
        }


       //广告位模块
        public function getAdInfo($link_id)
        {
          $info=$this->adService->getAdInfo($link_id);
          $info['ad_img']=$this->dd->del_img($info['ad_img']);
          $info['ad_desc']=$this->dd->deal_desc($info['ad_desc']);
          return $info;
        }



    }
