<?php
    /**
     * Created by PhpStorm.
     * User: Chaos
     * Date: 2018/9/20
     * Time: 13:17
     */
    require APPLICATION_PATH . '/modules/api/controllers/CommonController.php';

    class ShopcarController extends CommonController
    {
        protected $memberService;
        protected $shopCarService;
        protected $goodsService;
        protected $goodsProperyService;
        protected $dd;

        public function init()
        {
            parent::init();
            $this->memberService       = new MemberService();
            $this->shopCarService      = new ShopcarService();
            $this->goodsService        = new GoodsService();
            $this->goodsProperyService = new GoodsPropertyService();
            $this->dd                  = new Dd();
        }


        /**
         * 添加购物车
         * @return type
         */
        public function addAction()
        {
            $request      = $this->getRequest();
            $goods_id     = (int)$request->getParam('goods_id');
            $count        = (int)$request->getParam('count');
            $size_id      = Star_String::escape($request->getParam('size_id'));
            $color_id     = Star_String::escape($request->getParam('color_id'));
            $express_free = Star_String::escape($request->getParam('express_free'));
            $member_id    = $this->member_id;
            $member_info  = $this->memberService->getMemberInfo($member_id);
            if (empty($member_info)) {
                return $this->showJson(300, '微信号尚未绑定');
            }

            if (!$member_info['mobile']) {
                return $this->showJson(301, '您未绑定手机账号！');
            }

            if (empty($size_id)) {
                return $this->showJson(333, '尺码不能为空');
            }

            //            if (empty($color_id)) {
            //                return $this->showJson(333, '颜色不能为空');
            //            }


            $param = compact('member_id', 'color_id', 'size_id', 'goods_id');
            $ck_re = $this->shopCarService->ckShopCarInfo($param);
            if ($ck_re >= 2) {  //购物车商品大于2，报错
                return $this->showJson(111, '购物车数量出错！');
            }

            if ($ck_re == 1) {    //数量增加
                $info         = $this->shopCarService->getShopCarInfoByParam($param);
                $count        += $info['count'];
                $goods_info   = $this->goodsService->getGoodsInfoById($goods_id);
                $express_free = $count * $goods_info['express_free'];
                $price        = $express_free + $count * $goods_info['goods_price'];
                $edit_param   = compact('count', 'express_free', 'price');
                $re           = $this->shopCarService->editShopcar($info['shopcar_id'], $edit_param);
                return $this->showJson(200);
            }


            //添加购物车商品
            $param['express_free'] = $express_free;
            $param['count']        = ($count) ?: 1;
            $param['time_create']  = time();
            $param['time_update']  = time();
            $param['status']       = 1;

            if ($count <= 0) {
                return $this->showJson(201, '商品数不能小于0!');
            }

            if (empty($goods_id)) {
                return $this->showJson(202, '商品编号不能为空!');
            }

            $goods_info = $this->goodsService->getGoodsInfoById($goods_id);
            if (empty($goods_info)) {
                return $this->showJson(203, '该商品不能购买！');
            }

            if ($goods_info['is_on'] == 0) {
                return $this->showJson(203, '该商品已下架！');
            }

            if ($goods_info['is_check'] == 0) {
                return $this->showJson(203, '该商品未审核！');
            }

            if ($goods_info['is_check'] == 2) {
                return $this->showJson(2031, '该商品审核失败！');
            }

            $size_info = explode(',', $goods_info['goods_size_id']);
            if (!in_array($size_id, $size_info)) {
                return $this->showJson(204, '无相应尺寸！');
            }

            //            $color_info = $this->goodsProperyService->ckColor($color_id, $goods_info['color_unique_id']);
            //            if (empty($color_info)) {
            //                return $this->showJson(204, '无相应颜色！');
            //            }
            $param['brand_id'] = $goods_info['brand_id'];
            $param['price']    = $count * ($goods_info['goods_price'] + $goods_id['express_info']);
            $re                = $this->shopCarService->addShopCar($param);
            if ($re) {
                return $this->showJson(200);
            }
        }


        /**
         * 购物车列表
         * @return type
         */
        public function listAction()
        {
            $member_id   = $this->member_id;
            $member_info = $this->memberService->getMemberInfo($member_id);
            if (empty($member_info)) {
                return $this->showJson(300, '微信号尚未绑定');
            }

            if (!$member_info['mobile']) {
                return $this->showJson(301, '您未绑定手机账号！');
            }
            $request     = $this->getRequest();
            $shopcar_ids = $request->getParam('shopcar_ids');
            $param       = array();
            if ($shopcar_ids) {
                $param = explode(',', $shopcar_ids);
            }

            $shopcar_info = $this->shopCarService->getShopcarByBrand($member_id, $param);
            return $this->showJson(200, $shopcar_info);
        }


        /**
         * 加减购物车商品
         */
        public function exe1Action()
        {
            $member_id   = $this->member_id;
            $member_info = $this->memberService->getMemberInfo($member_id);
            if (empty($member_info)) {
                return $this->showJson(300, '微信号尚未绑定');
            }

            if (!$member_info['mobile']) {
                return $this->showJson(301, '您未绑定手机账号！');
            }
            $request    = $this->getRequest();
            $shopcar_id = (int)$request->getParam('shopcar_id');
            $type       = (int)$request->getParam('type');
            if (!in_array($type, array( 1, 2 ))) {
                return $this->showJson(201, '加减类型错误！');
            }

            if (empty($shopcar_id)) {
                return $this->showJson(202, '参数不能为空');
            }
            $shopcar_info = $this->shopCarService->getShopcarInfoById($shopcar_id);
            if (empty($shopcar_info)) {
                return $this->showJson(203, '参数有误！');
            }

            if ($shopcar_info['member_id'] != $this->member_id) {
                return $this->showJson(204, '信息不符！');
            }

            $goods_info = $this->goodsService->getGoodsInfoById($shopcar_info['goods_id']);
            if (empty($goods_info) || $goods_info['is_check'] != 1 || $goods_info['is_on'] == 0) {
                $re = $this->shopCarService->delShopcar(array( 'shopcar_id' => $shopcar_id ));
                return $this->showJson(213, '该商品已下架或删除，请重新选择！');
            }

            $count = ($type == 1) ? ++$shopcar_info['count'] : --$shopcar_info['count'];
            if ($count <= 0) {
                $re = $this->shopCarService->delShopcar(array( 'shopcar_id' => $shopcar_id ));
                return $this->showJson(200);
            }
            $express_free = $count * $goods_info['express_free'];
            $param        = array(
                'count' => $count,
                'time_update' => time(),
                'express_free' => $express_free,
                'price' => $goods_info['goods_price'] * $count + $express_free,
            );

            $re = $this->shopCarService->editShopcar($shopcar_id, $param);
            if ($re) {
                return $this->showJson(200);
            }

            return $this->showJson(205, '操作失败！');
        }


        /**
         * 批量删除购物车商品
         */
        public function delAction()
        {
            $member_id   = $this->member_id;
            $member_info = $this->memberService->getMemberInfo($member_id);
            if (empty($member_info)) {
                return $this->showJson(300, '微信号尚未绑定');
            }

            if (!$member_info['mobile']) {
                return $this->showJson(301, '您未绑定手机账号！');
            }
            $request     = $this->getRequest();
            $shopcar_ids = $request->getParam('shopcar_ids');
            if (empty($shopcar_ids)) {
                return $this->showJson(201, '参数不能为空！');
            }

            $list = explode(',', $shopcar_ids);

            //验证
            foreach ($list as $el) {
                $shopcar_info = $this->shopCarService->getShopcarInfoById($el);
                if (empty($shopcar_info)) {
                    return $this->showJson(201, '参数有误！');
                }

                if ($shopcar_info['member_id'] != $this->member_id) {
                    return $this->showJson(202, '信息不符！');
                }
            }

            $i = 0;
            foreach ($list as $el) {  //批量删除购物车商品
                $re = $this->shopCarService->delShopcar(array( 'shopcar_id' => $el ));
                if ($re) {
                    $i++;
                }
                unset($re);
            }

            if ($i == count($list)) {
                return $this->showJson(200);
            }
            return $this->showJson(330, '删除失败！');
        }


        /**
         * 编辑购物车
         * @return type
         */
        public function exeAction()
        {
            $member_id   = $this->member_id;
            $member_info = $this->memberService->getMemberInfo($member_id);
            if (empty($member_info)) {
                return $this->showJson(300, '微信号尚未绑定');
            }

            if (!$member_info['mobile']) {
                return $this->showJson(301, '您未绑定手机账号！');
            }
            $request   = $this->getRequest();
            $member_id = $this->member_id;
            $goods_id  = (int)$request->getParam('goods_id');
            $size_id   = Star_String::escape($request->getParam('size_id'));
            $color_id  = Star_String::escape($request->getParam('color_id'));
            $count     = (int)$request->getParam('count');
            if ($count <= 0) {
                return $this->showJson(211, '您所选购的商品数量必须大于等于1');
            }

            //            $count_re=$this->goodsService->ckCount($count, $size_id, $color_id, $goods_id);
            $count_re = $this->goodsService->ckCount($count, $size_id, $goods_id);
            if ($count_re) {
                return $this->showJson(311, '您所选购的商品数量超过库存限制！');
            }
            if (empty($size_id)) {
                return $this->showJson(333, '尺码不能为空');
            }

            //            if (empty($color_id)) {
            //                return $this->showJson(333, '颜色不能为空');
            //            }

            if (empty($goods_id)) {
                return $this->showJson(202, '商品编号不能为空!');
            }

            $goods_info = $this->goodsService->getGoodsInfoById($goods_id);
            if (empty($goods_info)) {
                return $this->showJson(203, '该商品不能购买！');
            }

            if ($goods_info['is_on'] == 0) {
                return $this->showJson(203, '该商品已下架！');
            }

            if ($goods_info['is_check'] == 0) {
                return $this->showJson(203, '该商品未审核！');
            }

            if ($goods_info['is_check'] == 2) {
                return $this->showJson(2031, '该商品审核失败！');
            }

            $size_info = Dd::dealExplode($goods_info['goods_size_id']);
            if (!in_array($size_id, $size_info)) {
                return $this->showJson(204, '无相应尺寸！');
            }

            $param = compact('member_id', 'color_id', 'size_id', 'goods_id');
            $ck_re = $this->shopCarService->ckShopCarInfo($param);

            //            $color_info = $this->goodsProperyService->ckColor($color_id, $goods_info['color_unique_id']);
            //            if (empty($color_info)) {
            //                return $this->showJson(204, '无相应颜色！');
            //            }

            if ($ck_re >= 2) {  //购物车商品大于2，报错
                return $this->showJson(111, '购物车数量出错！');
            }

            if ($ck_re == 1) {    //数量更新
                $info        = $this->shopCarService->getShopCarInfoByParam($param);
                $time_update = time();
                $edit_param  = compact('count', 'time_update');
                $re          = $this->shopCarService->editShopcar($info['shopcar_id'], $edit_param);
                return $this->showJson(200);
            }

            $param['count']       = $count;
            $param['brand_id']    = $goods_info['brand_id'];
            $param['time_update'] = time();
            $param['time_create'] = time();
            $param['time_create'] = time();
            $param['status']      = 1;
            $re                   = $this->shopCarService->addShopCar($param);
            if ($re) {
                return $this->showJson(200);
            }
            return $this->showJson(201, '操作失败！');

        }


    }