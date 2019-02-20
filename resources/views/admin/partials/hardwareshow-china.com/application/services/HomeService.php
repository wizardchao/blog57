<?php
    /**
     * Created by PhpStorm.
     * User: Chaos
     * Date: 2018/8/13
     * Time: 23:48
     */

    class HomeService
    {
        protected $manageConfigModel;
        protected $visitorModel;
        private $bannerModel;
        private $globalFoodModel;
        private $dataModel;
        private $activityModel;
        private $newsModel;
        private $config_info;
        protected $adService;
        protected $dd;

        /*
         * 构造函数
         */

        public function __construct()
        {
            $this->bannerModel       = new BannerModel();
            $this->globalFoodModel   = new GlobalFoodModel();
            $this->dataModel         = new DataModel();
            $this->activityModel     = new ActivityModel();
            $this->newsModel         = new NewsModel();
            $this->manageConfigModel = new ManageConfigModel();
            $this->visitorModel      = new VisitorModel();
            $config_id               = 1;
            $this->config_info       = $this->manageConfigModel->getConfigInfo($config_id);
            $this->utilsHelper=new UtilsHelper();
            $this->adService=new AdService();
            $this->dd=new Dd();
        }

        public function addBanner($param)
        {
            return $this->bannerModel->insert($param);
        }

        public function editBanner($id, $param)
        {
            $where = array(
                'banner_id' => $id,
            );
            $re    = $this->bannerModel->update($where, $param);
            if ($re == 0) {
                return $id;
            }
            return $re;
        }

        public function delBanner($id)
        {
            $where = array(
                'banner_id' => $id,
            );
            $param = array(
                'status' => -1,
            );
            return $this->bannerModel->update($where, $param);
        }

        public function getBannerInfo($id)
        {
            return $this->bannerModel->getBannerInfoById($id);
        }

        public function addGlobalFood($param)
        {
            return $this->globalFoodModel->insert($param);
        }

        public function editGlobalFood($global_food_id, $param)
        {
            $where = array(
                'globalfood_id' => $global_food_id,
            );
            $re    = $this->globalFoodModel->update($where, $param);
            if ($re == 0) {
                return $global_food_id;
            }
            return $re;
        }

        public function delGlobalFood($global_food_id)
        {
            $where = array(
                'globalfood_id' => $global_food_id,
            );
            $param = array(
                'status' => -1,
            );
            return $this->globalFoodModel->update($where, $param);
        }

        public function getGlobalFoodInfo($gid)
        {
            return $this->globalFoodModel->getGlobalFoodInfoById($gid);
        }


        public function getBanner($banner_type)
        {
            $page        = 0;
            $page_size   = 50;
            $param       = array(
                'banner_type' => $banner_type,
            );
            $banner_info = $this->getBannerInfoByPage($page, $page_size, $param);
            $banner_list = $banner_info['list'];

            foreach ($banner_list as &$list) {
                $list['banner_img'] = DOMAIN_IMG . $list['banner_img'];
                $list['banner_m_img'] = DOMAIN_IMG . $list['banner_m_img'];
            }
            return $banner_list;
        }


        /**
         * banner分页
         */
        public function getBannerInfoByPage($page, $page_size, $param)
        {
            $total     = $this->bannerModel->getAllCounts($param);
            $page      = Star_Page::setPage($page, $page_size, $total);
            $list      = $this->bannerModel->getBannerInfoByPage($page, $page_size, $param);
            $page_info = compact('page', 'page_size', 'total');
            $page_data = Star_Page::show($page_info);
            return array( 'page' => $page_data, 'total' => $total, 'list' => $list );
        }


        public function getDataInfo()
        {
            $page      = 0;
            $page_size = 50;
            $param     = array();
            $data_list = $this->dataModel->getDataInfoByPage($page, $page_size, $param);

            foreach ($data_list as &$list) {
                $list['data_link'] = ($list['data_link']) ? DOMAIN_IMG . $list['data_link'] : '/';
            }
            return $data_list;
        }

        public function getHotDataInfo()
        {
            $page      = 0;
            $page_size = ($this->config_info['home_data_count']) ?$this->config_info['home_data_count']: 3;
            $param     = array();
            $data_list = $this->dataModel->getDataInfoByPage($page, $page_size, $param);

            foreach ($data_list as &$list) {
                $list['data_link'] = ($list['data_link']) ? DOMAIN_IMG . $list['data_link'] : '/';
            }
            return $data_list;
        }


        public function getActivity()
        {
            $page          = 0;
            $page_size     = ($this->config_info['home_activity_count']) ?$this->config_info['home_activity_count']: 6;
            $param         = array(
                'is_home' => 1,
                'activity_category_id' => 1001,
            );
            $activity_list = $this->activityModel->getList($page, $page_size, $param);

            foreach ($activity_list as &$list) {
                $list['activity_link'] = ($list['activity_link']) ? DOMAIN_IMG . $list['activity_link'] : '/';
            }
            return $activity_list;
        }


        public function getNews()
        {
            $page      = 0;
            $page_size = ($this->config_info['home_news_count']) ?$this->config_info['home_news_count']: 3;
            $param     = array(
                'category_id' => 5,
                'home_show' => 1,
            );
            $news_list = $this->newsModel->getNewsInfoByPage($page, $page_size, $param);

            foreach ($news_list as &$list) {
                if ($list['type'] == 1) {
                    $list['news_link'] = 'news/detail?id=' . $list['news_id'];
                }
                $list['news_img'] = ($list['news_img'])?DOMAIN_IMG . $list['news_img']:'http://static.anufoodchina.huixianginc.com/img/news_pic.jpg';
                $list['news_desc']=$this->utilsHelper->utfSubstr($this->utilsHelper->del_label($list['news_content']), 0, 60);
            }
            return $news_list;
        }


        public function getHomeGlobalFoodInfo()
        {
            $page      = 0;
            $page_size = 100;
            $param=array();
            $info      = $this->getGlobalFoodInfoByPage($page, $page_size, $param);
            $list      = $info['list'];
            foreach ($list as &$item) {
                $item['globalfood_img'] = DOMAIN_IMG . $item['globalfood_img'];
            }
            return $list;
        }


        public function getGlobalFoodInfoByPage($page, $page_size, $param)
        {
            $total     = $this->globalFoodModel->getAllCounts($param);
            $page      = Star_Page::setPage($page, $page_size, $total);
            $list      = $this->globalFoodModel->getGlobalFoodInfoByPage($page, $page_size, $param);
            $page_info = compact('page', 'page_size', 'total');
            $page_data = Star_Page::show($page_info);
            return array( 'page' => $page_data, 'total' => $total, 'list' => $list );
        }

        public function getHomeVisitorInfo()
        {
            $page         = 0;
            $page_size    = 10;
            $param        = array();
            $visitor_info = $this->visitorModel->getVisitorInfoByPage($page, $page_size, $param);
            foreach ($visitor_info as &$item) {
                $item['visitor_img'] = $this->dd->del_img($item['visitor_img']);
            }
            return $visitor_info;
        }


        public function getAdList(){
          $param=array(
            'ad_home' => 1,
          );
          $ad_info=$this->adService->getAll($param);
           foreach($ad_info as &$el){
             $el['ad_img']=$this->dd->del_img($el['ad_img']);
           }
           return $ad_info;
        }
    }
