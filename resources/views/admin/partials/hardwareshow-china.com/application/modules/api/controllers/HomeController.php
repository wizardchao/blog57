<?php
    /**
     * Created by PhpStorm.
     * User: Chaos
     * Date: 2018/8/31
     * Time: 13:51
     */
    require APPLICATION_PATH . '/modules/api/controllers/CommonController.php';

    class HomeController extends CommonController
    {
        protected $homeService;
        protected $goodsModel;
        protected $data;

        public function init()
        {
            parent::init();
            $this->homeService = new HomeService();
            $this->data        = array();
            $this->goodsModel  = new GoodsModel();
        }


        public function setAction()
        {
            $request   = $this->getRequest();
            $page      = (int)$request->getParam('page');
            $page_size = 4;

            //轮播图列表
            $banner_list = $this->homeService->getBannerList();

            //活动
            $activity_list    = $this->homeService->getActivityList();
            $news_brand_list  = $activity_list[1];    //新品推荐
            $scene_match_list = $activity_list[2];     //场景搭配

            //热销设计
            $hot_design_list = $this->homeService->getHotDesignList();

            //品牌推荐
            $brand_rec_list = $this->homeService->getBrandRecList();

            //设计文化
            $article_list = $this->homeService->getArticleList($page, $page_size);

            //为您推荐
            $goods_recommend_list = $this->homeService->getGoodsRecList($page, $page_size, array());

            $this->data = compact('banner_list', 'news_brand_list', 'hot_design_list', 'scene_match_list', 'brand_rec_list', 'article_list', 'goods_recommend_list');
            return $this->showJson(200, $this->data);
        }


        /**
         * 场景搭配详情
         */
        public function scene_detailAction()
        {
            $request = $this->getRequest();
            $id      = (int)$request->getParam('id');
            if (empty($id)) {
                return $this->showJson(201, '参数不能为空');
            }
            $activity_info = $this->homeService->getActivityInfo($id);
            if (empty($activity_info)) {
                return $this->showJson(201, '参数有误');
            }

            $goods_arr = explode(',', $activity_info['goods_ids']);
            $param     = array( 'goods_id', 'thumbnail', 'goods_price', 'goods_title', 'goods_subtitle', 'goods_title_en', 'goods_subtitle_en' );
            $list      = array();

            foreach ($goods_arr as $val) {
                $info   = $this->goodsModel->getGoodsInfoById($val, $param);
                $list[] = array(
                    'id' => (int)$info['goods_id'],
                    'thumbnail' => ($info['thumbnail']) ? DOMAIN_FILE . $info['thumbnail'] : '',
                    'title' => $this->lang->is_cn($info['goods_title'], $info['goods_title_en']),
                    'subtitle' => $this->lang->is_cn($info['goods_subtitle'], $info['goods_subtitle_en']),
                    'is_favorite' => (int)$this->homeService->getGoodsFavorite($info['goods_id']),
                    'price' => $info['goods_price'],
                );
                unset($info);
            }

            $activity_info['goods_list'] = $list;
            $this->showJson(200, $activity_info);
        }

    }