<?php

    class BaseController extends Star_Controller_Action
    {
        protected $baseService;
        protected $aboutService;
        protected $dataDownloadService;
        protected $homeService;
        protected $adService;
        protected $linkService;
        protected $newsService;
        protected $dd;
        private $page_size=10;
        private $page=0;

        public function init()
        {
            $this->baseService         = new BaseService();
            $this->aboutService         = new AboutService();
            $this->dataDownloadService=new DataDownloadService();
            $this->newsService=new NewsService();
            $this->homeService=new HomeService();
            $this->adService=new AdService();
            $this->linkService=new LinkService();
            $this->dd=new Dd();
            $configInfo=$this->baseService->getConfig();
            $configInfo['company']=$this->dd->deal_desc($configInfo['company']);
            $configInfo['contact']=$this->dd->deal_desc($configInfo['contact']);
            $configInfo['contact_way']=$this->dd->deal_desc($configInfo['contact_way']);
            $configInfo['home_page_activity_desc']=$this->dd->deal_desc($configInfo['home_page_activity_desc']);
            $configInfo['home_page_news_desc']=$this->dd->deal_desc($configInfo['home_page_news_desc']);
            $configInfo['exhibitor_bg']=$this->dd->del_img($configInfo['exhibitor_bg']);
            $configInfo['miss_bg']=$this->dd->del_img($configInfo['miss_bg']);
            $configInfo['pic_bg']=$this->dd->del_img($configInfo['pic_bg']);
            $configInfo['activity_bg']=$this->dd->del_img($configInfo['activity_bg']);
            $configInfo['news_bg']=$this->dd->del_img($configInfo['news_bg']);

            $data_list=$this->dataDownloadService->getDataInfoByPage($this->page, $configInfo['home_data_count'], array());
            // Dd::dump($data_list);
            $global_list=$this->homeService->getGlobalFoodInfoByPage($this->page, $configInfo['home_activity_count'], array());
            $common_footer_ad_list=$this->adService->getFooterAd();
            $link_list=  $this->linkService->getLinkInfoByPage($this->page, $configInfo['home_news_count'],array('link_category_id' => 1002,'link_position_id' => 1,));

            $navInfo=$this->getNav();
            // Dd::dump($global_list['list']);
            $c_news_list=$this->newsService->getAllCategory();
            // Dd::dump($c_list);
            $data = array(
              'c_news_list' => $c_news_list,
              'link_list' => $link_list['list'],
              'common_footer_ad_list' => $common_footer_ad_list,
              'global_list' => $global_list['list'],
                'data_list' => $data_list['list'],
               'configInfo' => $configInfo,
               'navInfo' => $navInfo,
               'img' => $this->getImg(),
            );

            $this->view->assign($data);
        }

        public function getNav()
        {
            $pid=0;
            $firstNavInfo=$this->aboutService->getSecondChild($pid, 1);
            $list=array();
            foreach ($firstNavInfo as $el) {
                $id=$el['about_id'];
                $sub=$el['subnav'];
                $secondInfo=array();
                $secondInfo=$this->aboutService->getSecondChild($id, 1);  //二级分类
                if (count($secondInfo)) {
                    foreach ($secondInfo as &$rs) {
                        $rs['child']=$this->aboutService->getSecondChild($rs['about_id'], 1);  //三级分类
                    }
                }
                $list[$sub]=$secondInfo;
            }

            return compact('firstNavInfo', 'list');
        }


        public function getImg()
        {
            $productImg= array(
            0 => '/img/icons/icon-exhibitor-search__H60.svg',
            1 => '/img/icons/icon-exhibitor-register__H60.svg',
            2 => '/img/icons/icon-hall-plan__H60.svg',
            3 => '/img/icons/icon-matchmaking__H60.svg',
          );
            $eventImg=array(
            '0' => '/img/icons/icon-exhibitor-search__H60.svg',
            '1' => '/img/icons/icon-exhibitor-register__H60.svg',
            '2' => '/img/icons/icon-hall-plan__H60.svg',
          );

            return compact('productImg', 'eventImg');
        }
    }
