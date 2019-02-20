<?php
    /**
     * Created by PhpStorm.
     * User: Chaos
     * Date: 2018/9/21
     * Time: 12:40
     */
    require APPLICATION_PATH . '/modules/api/controllers/CommonController.php';

    class OrderController extends CommonController
    {
        protected $memberService;
        protected $shopcarService;
        protected $goodsService;
        protected $goodsProperyService;
        protected $orderService;
        protected $shopCarService;
        protected $brandService;
        protected $configService;
        protected $dd;
        private $info;

        public function init()
        {
            parent::init();
            $this->memberService       = new MemberService();
            $this->shopCarService      = new ShopcarService();
            $this->goodsService        = new GoodsService();
            $this->goodsProperyService = new GoodsPropertyService();
            $this->orderService        = new OrderService();
            $this->shopCarService      = new ShopcarService();
            $this->brandService        = new GoodsBrandService();
            $this->configService       = new ManageConfigService();
            $this->dd                  = new Dd();
            $this->info                = $this->configService->getWechatConfig();
        }


        /**
         * 确认生成订单
         */
        public function confirmAction()
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
            $city        = Star_String::escape($request->getParam('city'));
            $area        = Star_String::escape($request->getParam('area'));
            $province    = Star_String::escape($request->getParam('province'));
            $address     = Star_String::escape($request->getParam('address'));
            $name        = Star_String::escape($request->getParam('name'));
            $mobile      = Star_String::escape($request->getParam('mobile'));

            if (empty($city) || empty($area) || empty($province) || empty($address) || empty($name) || empty($mobile)) {
                return $this->showJson(204, '参数缺失！');
            }
            if (empty($shopcar_ids)) {
                return $this->showJson(201, '购物车编号不能为空！');
            }
            $shopcar_list = explode(',', $shopcar_ids);
            $shopcar_info = array();

            $total_arr = array();

            //购物车商品按品牌分类
            foreach ($shopcar_list as $el) {
                $info = $this->shopCarService->getShopcarInfoById($el);
                if (empty($info)) {
                    return $this->showJson(202, '购物车商品不存在！');
                }

//                $count_re = $this->goodsService->ckCount($info['count'], $info['size_id'], $info['color_id'], $info['goods_id']);
//                $count_re = $this->goodsService->ckCount($info['count'], $info['size_id'], $info['goods_id']);
//                if ($count_re) {
//                    return $this->showJson(311, '您所选购的商品数量超过库存限制！');
//                }


                if ($info['member_id'] != $member_id) {
                    return $this->showJson(203, '购物车信息不符！');
                }

                $shopcar_info[$info['brand_id']][] = $info;
                if (isset($total_arr[$info['brand_id']])) {
                    $total_arr[$info['brand_id']] += $info['count'] * ($info['goods_price'] + $info['express_free']);
                } else {
                    $total_arr[$info['brand_id']] = $info['goods_price'];
                }
            }

            //生成订单号
            $order_no     = $this->orderService->createOrderNo($member_id);
            $status       = 2;
            $order_status = 2;
            $time_create  = time();
            $time_update  = time();
            $param        = compact('name', 'mobile', 'city', 'area', 'province', 'address', 'status', 'order_status', 'time_create', 'time_update', 'member_id', 'order_no');

            $i                     = 0;
            $wechat_order_no_array = array();
            $total_goods_price     = 0;
            $total_express_free    = 0;

            foreach ($shopcar_info as $val) {
                $order_no_create = $order_no;
                $sub_order_no    = $order_no_create . ++$i;
                $order_price     = 0;
                $express_free    = 0;
                foreach ($val as $el) {
                    $goods_info = $this->goodsService->getGoodsInfoById($el['goods_id']);

                    $param['main_order_no'] = $order_no;
                    $param['order_no']      = $sub_order_no;
                    $param['sort_id']       = $i;
                    $param['goods_id']      = $el['goods_id'];
                    $param['brand_id']      = $el['brand_id'];
                    //                    $param['goods_price']     = $el['count'] * ($goods_info['goods_price'] + $goods_info['express_free']);
                    $param['goods_per_price']         = $goods_info['goods_price'];
                    $param['goods_size_id']           = $el['size_id'];
                    $param['goods_color_id']          = $el['color_id'];
                    $param['goods_count']             = $el['count'];
                    $param['express_free']            = $goods_info['express_free'];
                    $param['row_goods_price']         = $el['count'] * $goods_info['goods_price'];
                    $param['row_goods_express_free']  = $el['count'] * $goods_info['express_free'];
                    $param['row_total_price']         = $param['row_goods_price'] + $param['row_goods_express_free'];
                    $param['goods_category_relation'] = $goods_info['relation'];
                    $param['order_ip']                = $_SERVER["REMOTE_ADDR"];
                    $sub_order_re                     = $this->orderService->addOrder($param);
                    $shopcar_re                       = $this->shopCarService->editShopcar($el['shopcar_id'], array( 'status' => -2, 'time_update' => time(), ));
                    if ($sub_order_re && $shopcar_re) {
                        $wechat_order_no_array[] = $sub_order_re;
                        $order_price             += $param['row_goods_price'];
                        $express_free            += $param['row_goods_express_free'];
                    } else {
                        return $this->showJson(201, '订单出错！');
                    }

                }

                $order_no_param     = array(
                    'brand_goods_price' => $order_price,
                    'brand_goods_express_free' => $express_free,
                    'brand_total_price' => $order_price + $express_free,
                );
                $total_goods_price  += $order_price;
                $total_express_free += $express_free;
                $this->orderService->editOrderByOderNo($sub_order_no, $order_no_param);
            }
            $total_price = $total_goods_price + $total_express_free;
            $total_param = array(
                'total_goods_price' => $total_goods_price,
                'total_express_free' => $total_express_free,
                'total_price' => $total_price,
            );
            $this->orderService->editOrderByMainOderNo($order_no, $total_param);

            $data = array(
                'order_no' => $order_no . '_' . $this->dd->createString($i),
            );
            return $this->showJson(200, $data);
        }


        /**
         * 订单列表
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
            $request   = $this->getRequest();
            $page_size = 5;
            $page      = (int)$request->getParam('page');
            $type      = (int)Star_String::escape($request->getParam('order_status'));
            if ($type) {
                $type_arr = $this->orderService->getAllOrderStatus();
                if (!array_key_exists($type, $type_arr)) {
                    return $this->showJson(333, '订单状态错误');
                }
            }

            $param = array(
                'member_id' => $member_id,
                'order_type' => $type,
            );

            $order_list = $this->orderService->getOrderInfoByPage($page, $page_size, $param);
            $ck_page    = $this->utilsHelper->is_page($order_list['total'], $page, $page_size);
            if ($ck_page) {
                return $this->showJson(223, "超出页数范围！");
            }
            $list    = $order_list['list'];
            $re_list = array();
            $total   = 0;
            foreach ($list as $el) {
                $brand_id                 = (int)$el['brand_id'];
                $brand_info               = $this->brandService->getGoodsBrandInfoById($brand_id);
                $brand_logo               = ($brand_info['brand_logo']) ? DOMAIN_FILE . $brand_info['brand_logo'] : '';
                $order_no                 = $el['order_no'];
                $brand_goods_price        = (int)$el['brand_goods_price'];
                $brand_goods_express_free = (int)$el['brand_goods_express_free'];
                $brand_total_free         = (int)$el['brand_total_free'];
                $order_express_no         = $el['express_no'];
                $order_express_info       = $el['express_info'];
                $name                     = $el['name'];
                $mobile                   = $el['mobile'];
                $province                 = $el['province'];
                $city                     = $el['city'];
                $area                     = $el['area'];
                $address                  = $el['address'];
                $status                   = (int)$el['order_status'];
                $brand_info               = $this->brandService->getGoodsBrandInfoById($brand_id);
                $brand_title              = $brand_info['brand_title'];
                $info                     = $this->orderService->getOrderListByOrderNo($order_no);
                $goods_info               = $info['list'];
                $total                    = $info['total'];
                if (count($goods_info)) {
                    $re_list[] = array(
                        'order_no' => $order_no,
                        'brand_id' => $brand_id,
                        'brand_title' => $brand_title,
                        'brand_logo' => $brand_logo,
                        'order_status' => $status,
                        'brand_goods_price' => (int)$brand_goods_price,
                        'brand_goods_express_free' => (int)$brand_goods_express_free,
                        'brand_total_price' => (int)$el['brand_total_price'],
                        'order_express_no' => $order_express_no,
                        'order_express_company' => $el['order_express_company'],
                        'order_express_info' => $order_express_info,
                        'goods_total' => $total,
                        //                        'name' => $name,
                        //                        'mobile' => $mobile,
                        //                        'province' => $province,
                        //                        'city' => $city,
                        //                        'area' => $area,
                        //                        'address' => $address,
                        'goods_info' => $goods_info,
                    );
                }
                unset($total);
            }

            $data = array(
                'total' => (int)$order_list['total'],
                'page_total' => ceil($order_list['total'] / $page_size),
                'page_size' => $page_size,
                'list' => $re_list,
            );
            return $this->showJson(200, $data);

        }


        /**
         * 取消订单
         */
        public function cancelAction()
        {
            $member_id   = $this->member_id;
            $member_info = $this->memberService->getMemberInfo($member_id);
            if (empty($member_info)) {
                return $this->showJson(300, '微信号尚未绑定');
            }

            if (!$member_info['mobile']) {
                return $this->showJson(301, '您未绑定手机账号！');
            }
            $request = $this->getRequest();
            $ids     = $request->getParam('ids');
            if (empty($ids)) {
                return $this->showJson(201, '订单编号不能为空！');
            }
            $order_list = explode(',', $ids);
            foreach ($order_list as $el) {
                $info = $this->orderService->getOrderInfoByNoLimit($el);
                if (empty($info)) {
                    return $this->showJson(202, '订单商品不存在！');
                }

                if ($info['member_id'] != $member_id) {
                    return $this->showJson(203, '订单信息不符！');
                }
            }


            foreach ($order_list as $el) {
                $this->orderService->editOrderByOderNo($el, array( 'order_status' => 1, 'time_cancel' => time(),'cancel_type' => 1, ));
            }

            return $this->showJson(200);
        }


        /**
         * 订单详情
         * @return type
         */
        public function detailAction()
        {
            $member_id   = $this->member_id;
            $member_info = $this->memberService->getMemberInfo($member_id);
            if (empty($member_info)) {
                return $this->showJson(300, '微信号尚未绑定');
            }

            if (!$member_info['mobile']) {
                return $this->showJson(301, '您未绑定手机账号！');
            }
            $request  = $this->getRequest();
            $order_no = $request->getParam('order_no');
            if (empty($order_no)) {
                return $this->showJson(201, '订单编号不能为空！');
            }
            $info = $this->orderService->getOrderInfoByNoLimit($order_no);
            if (empty($info)) {
                return $this->showJson(201, '订单信息有误！');
            }
            $brand_id    = (int)$info['brand_id'];
            $brand_info  = $this->brandService->getGoodsBrandInfoById($brand_id);
            $brand_logo  = ($brand_info['brand_logo']) ? DOMAIN_FILE . $brand_info['brand_logo'] : '';
            $brand_title = $brand_info['brand_title'];
            $name        = $info['name'];
            $mobile      = $info['mobile'];
            $province    = $info['province'];
            $city        = $info['city'];
            $area        = $info['area'];
            $address     = $info['address'];
            $status      = (int)$info['order_status'];

            if ($info['member_id'] != $member_id) {
                return $this->showJson(203, '订单信息不符！');
            }

            $detail_info = $this->orderService->getOrderListByOrderNo($order_no);
            $goods_info  = $detail_info['list'];
            $total       = $detail_info['total'];

            $re_list = array(
                'order_no' => $order_no,
                'brand_id' => $brand_id,
                'brand_title' => $brand_title,
                'brand_logo' => $brand_logo,
                'order_status' => $status,
                'brand_goods_price' => (int)$info['brand_goods_price'],
                'brand_goods_express_free' => (int)$info['brand_goods_express_free'],
                'brand_total_price' => (int)$info['brand_total_price'],
                'order_express_no' => $info['express_no'],
                'order_express_company' => $info['express_company'],
                'order_express_info' => $info['express_info'],
                'goods_total' => $total,
                'name' => $name,
                'mobile' => $mobile,
                'province' => $province,
                'city' => $city,
                'area' => $area,
                'address' => $address,
                'wechat_trade_id' => $info['wechat_trade_id'],
                'tm_create' => ($info['time_create']) ? date('Y-m-d H:i:s', $info['time_create']) : '',
                'tm_send' => ($info['time_send']) ? date('Y-m-d H:i:s', $info['time_send']) : '',
                'tm_receive' => ($info['time_receive']) ? date('Y-m-d H:i:s', $info['time_receive']) : '',
                'tm_pay' => ($info['time_pay']) ? date('Y-m-d H:i:s', $info['time_pay']) : '',
                'goods_info' => $goods_info,
            );

            return $this->showJson(200, $re_list);

        }


        /**
         * 支付
         * @return type
         */
        public function payAction()
        {
            $request  = $this->getRequest();
            $order_no = $request->getParam('order_no');
            if (empty($order_no)) {
                return $this->showJson(202, '商户订单号不能为空！');
            }
            $config_info      = $this->info;
            $appid            = $config_info['app_id'];               // 小程序id
            $mch_id           = $config_info['mch_id'];              // 支付商户id
            $key              = $config_info['md5_key'];                // 商户的支付密钥
            $notify_url       = $config_info['notify_url'];      // 微信服务器异步通知
            $spbill_create_ip = $_SERVER['REMOTE_ADDR'];    // 客户端ip

            $openid = $this->memberService->getOpenIdById($this->member_id);                           // 用户openid
            if (empty($openid)) {
                return $this->showJson(222, '参数错误！');
            } elseif (is_array($openid)) {
                return $this->showJson($openid['status'], $openid['message']);
            }

            //解析商户订单号
            //            $order_no   = '180923115524111_1-2';                       // 订单编号
            $order_info = $this->orderService->getOrderByMuch($order_no);
            $body       = $order_info['order_desc'];
            $total_fee  = $order_info['total_price'];
            $weixinPay  = new WechatPayService($appid, $openid, $mch_id, $key, $order_no, $body, $total_fee, $notify_url, $spbill_create_ip);
            // 发起微信支付
            $result = $weixinPay->pay();
            if ($result['code'] == 0) {      // 统一下单
                return $this->showJson(201, '下单出错！');
            }

            if ($result['code'] == 1) {
                //                $data = array(
                //
                //                    'timeStamp' => $result['msg']['timeStamp'],
                //                    'nonceStr' => $result['msg']['nonceStr'],
                //                    'package' => $result['msg']['package'],
                //                    'signType' => $result['msg']['signType'],
                //                    'paySign' => $result['msg']['paySign'],
                //                );
                $data = $result['msg'];
                return $this->showJson(200, $data);
            }
            return $this->showJson(202, '下单异常！');
        }


        /**
         * 提醒发货
         */
        public function remindAction()
        {
            $member_id   = $this->member_id;
            $member_info = $this->memberService->getMemberInfo($member_id);
            if (empty($member_info)) {
                return $this->showJson(300, '微信号尚未绑定');
            }

            if (!$member_info['mobile']) {
                return $this->showJson(301, '您未绑定手机账号！');
            }
            $request = $this->getRequest();
            $id      = $request->getParam('id');

            if (empty($id)) {
                return $this->showJson(201, '订单编号不能为空！');
            }

            $info = $this->orderService->getOrderInfoByNoLimit($id);
            if (empty($info)) {
                return $this->showJson(202, '订单商品不存在！');
            }

            if ($info['member_id'] != $member_id) {
                return $this->showJson(203, '订单信息不符！');
            }

            if ($info['order_status'] == 3) {
                $ck_re = $this->orderService->ckRemind($member_id, $id);

                if (is_array($ck_re)) {
                    return $this->showJson($ck_re['status'], $ck_re['message']);
                }

                $order_no   = $id;
                $brand_info = $this->brandService->getGoodsBrandInfoById($info['brand_id']);
                $content    = '尊敬的' . $brand_info['brand_username'] . '您好，提醒您订单号：' . $order_no . '的买家已经付款，请您及时发货噢。';
                $param      = array(
                    'member_id' => $member_id,
                    'brand_id' => $info['brand_id'],
                    'order_no' => $id,
                    'time_create' => time(),
                    'time_update' => time(),
                    'status' => 1,
                    'content' => $content,
                );

                $re = $this->orderService->insertOrderRemind($param);
                if ($re) {
                    $messageService = new MessageService();
                    if ($brand_info['brand_username'] && $brand_info['mobile']) {
                        // $data = $messageService->sendCodeTencent($brand_info['mobile'], $content, MESSAGEURL, MESS_APPID, MESS_APPKEY);
                        // if ($data['status'] == 200) {
                            return $this->showJson(200);
                        // }
                    }
                    // return $this->showJson(204, '未能成功提醒！');
                }
            }

            if ($info['order_status'] == 5) {
                return $this->showJson(203, '您已收货！');
            }
            return $this->showJson(206, '请先确认订单状态是待发货状态！');
        }


        /**
         * 确认收货
         */
        public function confirm_receiptAction()
        {
            $member_id   = $this->member_id;
            $member_info = $this->memberService->getMemberInfo($member_id);
            if (empty($member_info)) {
                return $this->showJson(300, '微信号尚未绑定');
            }

            if (!$member_info['mobile']) {
                return $this->showJson(301, '您未绑定手机账号！');
            }
            $request = $this->getRequest();
            $id      = $request->getParam('id');
            if (empty($id)) {
                return $this->showJson(201, '订单编号不能为空！');
            }
            $info = $this->orderService->getOrderInfoByNoLimit($id);
            if (empty($info)) {
                return $this->showJson(202, '订单商品不存在！');
            }

            if ($info['member_id'] != $member_id) {
                return $this->showJson(203, '订单信息不符！');
            }

            if ($info['order_status'] == 5) {
                return $this->showJson(204, '您已收货，请勿重复点击！');
            }

            if ($info['order_status'] == 4) {
                $re = $this->orderService->editOrderByOderNo($id, array( 'order_status' => 5, 'time_receive' => time() ));
                if ($re) {
                    return $this->showJson(200);
                }
                return $this->showJson(205, '确认收货失败！');
            }
            return $this->showJson(206, '请先确认订单状态是待收货状态！');

        }


        public function back_reasonAction()
        {
            $reason_list = $this->orderService->getOrderBackReason();
            $data        = $reason_list;
            return $this->showJson(200, $data);
        }


        /**
         * 退货申请
         */
        public function back_applyAction()
        {
            $member_id   = $this->member_id;
            $member_info = $this->memberService->getMemberInfo($member_id);
            if (empty($member_info)) {
                return $this->showJson(300, '微信号尚未绑定');
            }

            if (!$member_info['mobile']) {
                return $this->showJson(301, '您未绑定手机账号！');
            }
            $request              = $this->getRequest();
            $order_no             = $request->getParam('id');
            $back_express_no      = $request->getParam('back_express_no');
            $back_express_company = $request->getParam('back_express_company');
            $back_reason          = $request->getParam('back_reason');
            if (empty($order_no)) {
                return $this->showJson(201, '订单编号不能为空！');
            }

            if (empty($back_express_no)) {
                return $this->showJson(212, '退货物流单号不能为空！');
            }

            if (empty($back_express_company)) {
                return $this->showJson(214, '退货物流公司不能为空！');
            }

            if (empty($back_reason)) {
                return $this->showJson(214, '退货理由不能为空！');
            }

            $info = $this->orderService->getOrderInfoById($order_no);
            if (empty($info)) {
                return $this->showJson(202, '订单不存在！');
            }
            if ($info['member_id'] != $member_id) {
                return $this->showJson(213, '订单信息不符！');
            }

            if ($info['order_status'] == 5) {
                if ($info['back_status'] == 2) {
                    return $this->showJson(203, '您已申请退货！');
                }

                if ($info['back_status'] == 3) {
                    return $this->showJson(204, '您退货已审核成功！');
                }

                $time_back_apply = time();
                $time_update     = time();
                $back_status     = 2;
                $param           = compact('back_express_no', 'back_express_company', 'back_reason', 'time_back_apply', 'time_update', 'back_status');
                $re              = $this->orderService->editOrder($order_no, $param);
                if ($re) {
                    return $this->showJson(200);
                }
            }

            return $this->showJson(205, '请确认你已收货！');
        }


        /**
         * 退货商品列表
         */
        public function back_goods_listAction()
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
            $page       = (int)$request->getParam('pages');
            $page_size  = 6;
            $param      = array(
                'member_id' => $member_id,
                'back' => 1,
                'is_back' => 1,
            );
            $order_list = $this->orderService->getBackOrderInfoByPage($page, $page_size, $param);
            $ck_page    = $this->utilsHelper->is_page($order_list['total'], $page, $page_size);
            if ($ck_page) {
                return $this->showJson(223, "超出页数范围！");
            }

            $list = array();
            foreach ($order_list['list'] as $el) {
                $list[] = array(
                    'order_id' => (int)$el['id'],
                    'goods_id' => (int)$el['goods_id'],
                    'order_no' => $el['order_no'],
                    //                    'brand_id' => (int)$el['brand_id'],
                    'order_status' => (int)$el['order_status'],
                    'back_status' => (int)$el['back_status'],
                    'row_goods_price' => (int)$el['row_goods_price'],
                    'row_goods_express_free' => (int)$el['row_goods_express_free'],
                    'row_total_price' => (int)$el['row_total_price'],
                    'goods_per_price' => (int)$el['goods_per_price'],
                    'express_free' => (int)$el['express_free'],
                    //                    'time_pay' => ($el['time_pay']) ? date('Y-m-d H:i:s', $el['time_pay']) : '',
                    //                    'time_back_apply' => ($el['time_back_apply']) ? date('Y-m-d H:i:s', $el['time_back_apply']) : '',
                    //                    'back_express_no' => $el['back_express_no'],
                    //                    'back_express_info' => $el['back_express_info'],
                    //                    'back_express_company' => $el['back_express_company'],
                    'goods_thumbnail' => ($el['thumbnail']) ? DOMAIN_FILE . $el['thumbnail'] : '',
                    'goods_title' => $el['goods_title'],
                    'goods_subtitle' => $el['goods_subtitle'],
                    'goods_count' => (int)$el['goods_count'],
                    'goods_size_id' => (int)$el['goods_size_id'],
                    'goods_size_title' => $this->shopCarService->getSizeTitle($el['goods_size_id']),
                    'goods_color_id' => (int)$el['goods_color_id'],
                    'goods_color_title' => $this->shopCarService->getColorTitle($el['goods_color_id']),
                );
            }
            $total      = $order_list['total'];
            $page_total = ceil($total / $page_size);
            $data       = array(
                'total' => (int)$total,
                'page_total' => $page_total,
                'page_size' => $page_size,
                'list' => $list,
            );

            return $this->showJson(200, $data);
            //                         = $request->getParam('id');

        }


        /**
         * 退货详情
         * @return type
         */
        public function back_goods_detailAction()
        {
            $member_id   = $this->member_id;
            $member_info = $this->memberService->getMemberInfo($member_id);
            if (empty($member_info)) {
                return $this->showJson(300, '微信号尚未绑定');
            }

            if (!$member_info['mobile']) {
                return $this->showJson(301, '您未绑定手机账号！');
            }
            $request  = $this->getRequest();
            $order_no = $request->getParam('id');
            if (empty($order_no)) {
                return $this->showJson(201, '订单编号不能为空！');
            }
            $info = $this->orderService->getOrderInfoById($order_no);
            if (empty($info)) {
                return $this->showJson(202, '订单不存在！');
            }

            if ($info['member_id'] != $member_id) {
                return $this->showJson(213, '订单信息不符！');
            }

            $goods_info = $this->goodsService->getGoodsInfoById($info['goods_id']);
            $data       = array(
                'order_id' => (int)$info['id'],
                'order_no' => $info['order_no'],
                //                'brand_id' => (int)$info['brand_id'],
                'order_status' => (int)$info['order_status'],
                'back_status' => (int)$info['back_status'],
                'row_goods_price' => (int)$info['row_goods_price'],
                'row_goods_express_free' => (int)$info['row_goods_express_free'],
                'row_total_price' => (int)$info['row_total_price'],
                'goods_per_price' => (int)$info['goods_per_price'],
                'goods_count' => (int)$info['goods_count'],
                'express_free' => $info['express_free'],
                'time_create' => ($info['time_create']) ? date('Y-m-d H:i:s', $info['time_create']) : '',
                'time_send' => ($info['time_send']) ? date('Y-m-d H:i:s', $info['time_send']) : '',
                'time_receive' => ($info['time_receive']) ? date('Y-m-d H:i:s', $info['time_receive']) : '',
                'time_pay' => ($info['time_pay']) ? date('Y-m-d H:i:s', $info['time_pay']) : '',
                'time_back_apply' => ($info['time_back_apply']) ? date('Y-m-d H:i:s', $info['time_back_apply']) : '',
                'time_back_confirm' => ($info['time_back_confirm']) ? date('Y-m-d H:i:s', $info['time_back_confirm']) : '',
                'back_express_no' => $info['back_express_no'],
                'back_express_info' => $info['back_express_info'],
                'back_express_company' => $info['back_express_company'],
                'back_express_reason' => $info['back_express_reason'],
                'wechat_trade_id' => $info['wechat_trade_id'],
                'goods_thumbnail' => ($goods_info['thumbnail']) ? DOMAIN_FILE . $goods_info['thumbnail'] : '',
                'goods_title' => $goods_info['goods_title'],
                'goods_subtitle' => $goods_info['goods_subtitle'],
                'goods_count' => (int)$info['goods_count'],
                'goods_size_id' => (int)$info['goods_size_id'],
                'goods_size_title' => $this->shopCarService->getSizeTitle($info['goods_size_id']),
                'goods_color_id' => (int)$info['goods_color_id'],
                'goods_color_title' => $this->shopCarService->getColorTitle($info['goods_color_id']),
            );

            return $this->showJson(200, $data);
        }


    }