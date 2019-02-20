<?php
    /**
     * Created by PhpStorm.
     * User: Chaos
     * Date: 2018/8/31
     * Time: 15:40
     */

    class GoodsController extends Star_Controller_Action
    {

        protected $goodsService;
        protected $homeService;
        protected $brandService;
        protected $utilsHelper;
        protected $goodsPropertyServicve;
        private $page_size;

        public function init()
        {
            $this->goodsService          = new GoodsService();
            $brandService                = new GoodsBrandService();
            $this->brandService          = new ApiBrandService($brandService);
            $this->homeService           = new HomeService();
            $this->goodsPropertyServicve = new GoodsPropertyService();
            $this->utilsHelper           = new UtilsHelper();
            $this->page_size             = 4;
        }


        /**
         * 商品分类
         */
        public function categoryAction()
        {
            $data = $this->goodsService->getApiGoodsCate();
            return $this->showJson(200, $data);
        }


        /**
         * 商品列表
         */
        public function listAction()
        {
            $request    = $this->getRequest();
            $page       = (int)$request->getParam('page');
            $p_cid      = (int)$request->getParam('cid');
            $sort_type  = (int)$request->getParam('sort_type');
            $sort_value = $request->getParam('sort_value');
            $brand_id   = (int)$request->getParam('brand_id');
            $is_home   = (int)$request->getParam('is_home');
            $is_on      = 1;
            $is_check=1;
            $param      = compact('is_home','p_cid', 'sort_type', 'sort_value', 'is_on', 'brand_id','is_check');
            if($p_cid){
              $param['is_rand']=1;
            }
            $data    = $this->homeService->getGoodsRecList($page, $this->page_size, $param);
            $ck_page = $this->utilsHelper->is_page($data['total'], $page, $this->page_size);
            if ($ck_page) {
                return $this->showJson(223, "超出页数范围！");
            }
            return $this->showJson(200, $data);
        }


        /**
         * 商品颜色
         * @return type
         */
        public function colorAction()
        {
            $data = $this->goodsPropertyServicve->getApiAllGoodColor();
            return $this->showJson(200, $data);
        }


        /**
         * 商品尺寸
         * @return type
         */
        public function sizeAction()
        {
            $data = $this->goodsPropertyServicve->getAllGoodSize();
            return $this->showJson(200, $data);
        }


        /**
         * 商品详情
         */
        public function detailAction()
        {
            $request  = $this->getRequest();
            $goods_id = (int)$request->getParam('id');
            if (empty($goods_id)) {
                return $this->showJson(201, '参数不能为空！');
            }

            $goodsApiService = new ApiGoodsService($this->goodsService);
            $goods_info      = $goodsApiService->getGoodsInfoById($goods_id);
            if (empty($goods_info)) {
                return $this->showJson(202, '参数信息有误！');
            }

            if ($goods_info['is_on'] != 1) {
                return $this->showJson(203, '该商品已下架！');
            }

            if ($goods_info['is_check'] != 1) {
                return $this->showJson(203, '该商品未审核！');
            }
            unset($goods_info['is_on']);
            unset($goods_info['is_check']);
            return $this->showJson(200, $goods_info);


        }

    }
