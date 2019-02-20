<?php
    /**
     * Created by PhpStorm.
     * User: Chaos
     * Date: 2018/9/20
     * Time: 10:31
     */
    require APPLICATION_PATH . '/modules/api/controllers/CommonController.php';

    class MemberController extends CommonController
    {
        protected $memberService;
        protected $dd;
        protected $avatarSave;
        protected $goodsService;
        protected $brandService;

        public function init()
        {
            parent::init();
            $this->memberService = new MemberService();
            $this->dd            = new Dd();
            $this->avatarSave    = new WechatAvatarSave();
            $this->goodsService  = new GoodsService();
            $this->brandService  = new GoodsBrandService();
            $this->homeService   = new HomeService();
        }


        /**
         * 用户登录
         */
        public function userinfoAction()
        {
            $request  = $this->getRequest();
            $code     = $request->getParam('code');
            $iv       = $request->getParam('iv');
            $userdata = $request->getParam('userdata');
            $url      = 'https://api.weixin.qq.com/sns/jscode2session?appid=' . APPID . '&secret=' . APPSECRET . '&js_code=' . $code . '&grant_type=authorization_code';

            $re = $this->dd->getUrlData(array(), $url);
            if (isset($re['errcode']) && $re['errcode']) {
                return $this->showJson(222, '请核对信息再重试！');
            }
            $openid      = $re['openid'];
            $session_key = $re['session_key'];
            $ck_re       = $this->memberService->ckOpenid($openid);

            if (!$ck_re) {     //用户信息入库
                $user_info = $this->memberService->encryptData($session_key, $iv, $userdata);
                $param     = array(
                    'wechat_openid' => $user_info['openId'],
                    'wechat_nickname' => $user_info['nickName'],
                    'gender' => (int)$user_info['gender'],
                    'city' => $user_info['city'],
                    'province' => $user_info['province'],
                    'country' => $user_info['country'],
                    'wechat_avatar' => $user_info['avatarUrl'],
                    'avatar' => $this->avatarSave->save_avatar($user_info['avatarUrl']),
                    'time_create' => $user_info['watermark']['timestamp'],
                    'ip_create' => $_SERVER["REMOTE_ADDR"],
                );

                $re = $this->memberService->insertMember($param);
                if ($re) {
                    unset($user_info['openId']);
                    unset($user_info['watermark']);
                    $user_info['username']    = '';
                    $user_info['time_create'] = $param['time_create'];
                    $user_info['ip_create']   = $param['ip_create'];
                    $user_info['mobile']      = '';
                    $user_info['identify']    = 1;
                    $user_info['member_id']   = (int)$re;
                    $mem_key                  = md5($user_info['member_id'] . PRIVATE_KEY);
                    $param['member_id']       = (int)$re;
                    Star_Cache::set($mem_key, base64_encode(json_encode($param)));
                    $user_info['order_prepay_count']     = 0;
                    $user_info['order_presend_count']    = 0;
                    $user_info['order_prereceive_count'] = 0;
                    $userinfo['order_back_count']        = 0;
                    $userinfo['order_total_count']       = 0;
                    $user_info['auth_sid']               = session_id();
                    $user_info['auth']                   = $mem_key;
                    return $this->showJson(200, $user_info);
                }
                return $this->showJson(201, '登录失败');
            }
            $user_info = $this->memberService->getMemberInfoByOpenid($openid);


            $mem_key = md5($user_info['member_id'] . PRIVATE_KEY);

            $order_info = $this->memberService->getOrderStatus($this->member_id);
            $param      = array(
                'username' => $user_info['username'],
                'nickName' => $user_info['wechat_nickname'],
                'gender' => (int)$user_info['gender'],
                'city' => $user_info['city'],
                'province' => $user_info['province'],
                'country' => $user_info['country'],
                'avatarUrl' => ($this->dd->del_img($user_info['avatar'])) ?: $user_info['wechat_avatar'],
                'reg_time' => $user_info['time_reg'] ? date('Y-m-d H:i:s', $user_info['time_reg']) : '',
                'reg_ip' => $user_info['ip_reg'],
                'mobile' => $user_info['mobile'],
                'member_id' => (int)$user_info['member_id'],
                'order_prepay_count' => (int)$order_info['order_prepay_count'],
                'order_presend_count' => (int)$order_info['order_presend_count'],
                'order_prereceive_count' => (int)$order_info['order_prereceive_count'],
                'order_back_count' => (int)$order_info['order_back_count'],
                'order_total_count' => (int)$order_info['order_total_count'],
            );
            if (!Star_Cache::get($mem_key)) {
                Star_Cache::set($mem_key, base64_encode(json_encode($param)));
            }

            $sessionId         = session_id();
            $param['auth_sid'] = $sessionId;
            $param['auth']     = $mem_key;
            return $this->showJson(200, $param);
        }


        /**
         * 收货地址
         */
        public function address_listAction()
        {
            $member_id    = $this->member_id;
            $member_info = $this->memberService->getMemberInfo($member_id);
            if (empty($member_info)) {
                return $this->showJson(300, '微信号尚未绑定');
            }

            if (!$member_info['mobile']) {
                return $this->showJson(301, '您未绑定手机账号！');
            }
            $request      = $this->getRequest();
            $page         = (int)Star_String::escape($request->getParam('page'));
            $page_size    = 50;
            $address_list = $this->memberService->getMemberAddress($page, $page_size, $member_id);
            $ck_page      = $this->utilsHelper->is_page($address_list['total'], $page, $page_size);
            if ($ck_page) {
                return $this->showJson(223, "超出页数范围！");
            }
            return $this->showJson(200, $address_list);
        }


        /**
         * 收货地址编辑
         */
        public function address_aeAction()
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
            $address_id  = (int)Star_String::escape($request->getParam('address_id'));
            $is_default  = (int)Star_String::escape($request->getParam('is_default'));
            $province    = Star_String::escape($request->getParam('province'));
            $city        = Star_String::escape($request->getParam('city'));
            $area        = Star_String::escape($request->getParam('area'));
            $address     = Star_String::escape($request->getParam('address'));
            $mobile      = Star_String::escape($request->getParam('mobile'));
            $name        = Star_String::escape($request->getParam('name'));
            $title       = Star_String::escape($request->getParam('title'));
            $time_update = time();
            $param       = compact('member_id', 'province', 'city', 'area', 'is_default', 'address', 'mobile', 'name', 'time_update', 'title');
            $ck_default  = $this->memberService->ck_defaultAction($member_id);
            if (empty($member_id) && empty($province) && empty($city) && empty($area) && empty($address) && empty($name) && empty($mobile)) {
                return $this->showJson(201, '信息不全！');
            }
            if (empty($address_id)) {  //添加
                if ($ck_default == 1 && $is_default == 1) {
                    $re = $this->memberService->editMemberAddressByDefault(1);
                }
                $param['time_create'] = $time_update;
                $param['status']      = 1;
                $re                   = $this->memberService->addMemberAddress($param);
                if ($re) {
                    return $this->showJson(200);
                }
                return $this->showJson(201, '未成功添加！');
            }

            //编辑
            $address_info = $this->memberService->getMemberAddressById($address_id);
            if (empty($address_info)) {
                return $this->showJson(402, '地址信息为空！');
            }

            if ($ck_default == 1 && $is_default == 1) {
                $re = $this->memberService->editMemberAddressByDefault(1);
            }

            if ($address_info['member_id'] != $member_id) {
                return $this->showJson(403, '信息不符！');
            }

            $re = $this->memberService->editMemberAddress($address_id, $param);
            if ($re) {
                return $this->showJson(200);
            } else {
                return $this->showJson(202, '编辑未成功！');
            }
        }


        /**
         * 删除地址
         */
        public function address_delAction()
        {
            $member_id  = $this->member_id;
            $member_info = $this->memberService->getMemberInfo($member_id);
            if (empty($member_info)) {
                return $this->showJson(300, '微信号尚未绑定');
            }

            if (!$member_info['mobile']) {
                return $this->showJson(301, '您未绑定手机账号！');
            }
            $request    = $this->getRequest();
            $address_id = (int)Star_String::escape($request->getParam('address_id'));

            if (empty($address_id)) {
                return $this->showJson(401, '参数不能为空！');
            }
            $address_info = $this->memberService->getMemberAddressById($address_id);
            if (empty($address_info)) {
                return $this->showJson(402, '地址信息为空！');
            }

            if ($address_info['member_id'] != $member_id) {
                return $this->showJson(403, '信息不符！');
            }
            $re = $this->memberService->delMemberAddress($address_id);
            if ($re) {
                return $this->showJson(200);
            }

            return $this->showJson(203, '删除失败');
        }


        /**
         * 收藏&取消
         * @return type
         */
        public function favorite_aeAction()
        {
            $member_id = $this->member_id;
            $member_info = $this->memberService->getMemberInfo($member_id);
            if (empty($member_info)) {
                return $this->showJson(300, '微信号尚未绑定');
            }

            if (!$member_info['mobile']) {
                return $this->showJson(301, '您未绑定手机账号！');
            }
            $request   = $this->getRequest();
            $type      = (int)Star_String::escape($request->getParam('type'));
            $type_id   = (int)Star_String::escape($request->getParam('type_id'));

            if (empty($type_id)) {
                return $this->showJson(211, '参数不能为空！');
            }

            $is_favorite = 1;
            $time_update = time();
            $time_create = time();
            $status      = 1;
            $ip          = $_SERVER["REMOTE_ADDR"];
            if ($type == 1) {  //收藏商品
                $goods_id   = $type_id;
                $goods_info = $this->goodsService->getGoodsInfoById($goods_id);
                if (empty($goods_info)) {
                    return $this->showJson(213, '参数有误！');
                }

                if ($goods_info['is_on'] == 0) {
                    return $this->showJson(214, '该商品已下架！');
                }

                if ($goods_info['is_check'] == 0) {
                    return $this->showJson(214, '该商品未审核！');
                }

                if ($goods_info['is_check'] == 2) {
                    return $this->showJson(2141, '该商品审核失败！');
                }

                $ck_info = $this->memberService->ckFavorite($type_id, $type, $member_id);

                if ($ck_info) {
                    $fav_info = $this->memberService->getFavorite($type_id, $type, $member_id);
                    $re       = $this->memberService->delFavorite($fav_info['id']);
                    return $this->showJson(200, array( 'is_favorite' => 0 ));
                }

                $param  = compact('member_id', 'is_favorite', 'time_update', 'time_create', 'status', 'type', 'type_id', 'ip');
                $fav_re = $this->memberService->addFavorite($param);
                if ($fav_re) {
                    return $this->showJson(200, array( 'is_favorite' => 1 ));
                }
                return $this->showJson(229, '收藏失败！');
            }

            if ($type == 2) {  //收藏品牌
                $brand_id   = $type_id;
                $brand_info = $this->brandService->getGoodsBrandInfoById($brand_id);
                if (empty($brand_info)) {
                    return $this->showJson(113, '参数有误！');
                }
                $ck_info = $this->memberService->ckFavorite($type_id, $type, $member_id);
                if ($ck_info) {
                    $fav_info = $this->memberService->getFavorite($type_id, $type, $member_id);

                    $re = $this->memberService->delFavorite($fav_info['id']);
                    return $this->showJson(200, array( 'is_favorite' => 0 ));
                }

                $param  = compact('member_id', 'is_favorite', 'time_update', 'time_create', 'status', 'type', 'type_id', 'ip');
                $fav_re = $this->memberService->addFavorite($param);
                if ($fav_re) {
                    return $this->showJson(200, array( 'is_favorite' => 1 ));
                }
                return $this->showJson(339, '收藏失败！');

            }

            return $this->showJson(212, '点赞类型错误！');
        }


        /**
         * 收藏列表
         */
        public function favorite_listAction()
        {
            $member_id = $this->member_id;
            $member_info = $this->memberService->getMemberInfo($member_id);
            if (empty($member_info)) {
                return $this->showJson(300, '微信号尚未绑定');
            }

            if (!$member_info['mobile']) {
                return $this->showJson(301, '您未绑定手机账号！');
            }
            $request   = $this->getRequest();
            $page      = (int)$request->getParam('page');
            $type      = (int)Star_String::escape($request->getParam('type'));
            $page_size = 6;
            if (!in_array($type, array( 1, 2 ))) {
                return $this->showJson(201, '收藏类型错误！');
            }

            $param = array(
                'fav' => $type,
                'member_id' => $member_id,
            );
            if ($type == 1) {
                $data = $this->homeService->getGoodsRecList($page, $page_size, $param);
            } else {
                $brandService = new ApiBrandService($this->brandService);
                $data         = $brandService->getBrandInfoByPage($page, $page_size, $param);
            }

            $ck_page = $this->utilsHelper->is_page($data['total'], $page, $page_size);
            if ($ck_page) {
                return $this->showJson(223, "超出页数范围！");
            }
            return $this->showJson(200, $data);


        }


        /**
         * 地址详情
         * @return type
         */
        public function address_detailAction()
        {
            $member_id  = $this->member_id;
            $member_info = $this->memberService->getMemberInfo($member_id);
            if (empty($member_info)) {
                return $this->showJson(300, '微信号尚未绑定');
            }

            if (!$member_info['mobile']) {
                return $this->showJson(301, '您未绑定手机账号！');
            }
            $request    = $this->getRequest();
            $address_id = (int)Star_String::escape($request->getParam('id'));
            if (empty($address_id)) {
                return $this->showJson(222, '参数不能为空！');
            }

            $address_info = $this->memberService->getMemberAddressById($address_id);
            if (empty($address_info)) {
                return $this->showJson(223, '参数有误！');
            }

            if ($address_info['member_id'] != $member_id) {
                return $this->showJson(223, '用户信息不符！');
            }

            $data = array(
                'id' => (int)$address_info['id'],
                'is_default' => (int)$address_info['is_default'],
                'name' => $address_info['name'],
                'mobile' => $address_info['mobile'],
                'province' => $address_info['province'],
                'city' => $address_info['city'],
                'area' => $address_info['area'],
                'address' => $address_info['address'],
                'title' => $address_info['title'],
            );

            $this->showJson(200, $data);
        }


        /**
         * 衣柜
         */
        public function wardrobeAction()
        {
            $member_id = $this->member_id;
            $member_info = $this->memberService->getMemberInfo($member_id);
            if (empty($member_info)) {
                return $this->showJson(300, '微信号尚未绑定');
            }

            if (!$member_info['mobile']) {
                return $this->showJson(301, '您未绑定手机账号！');
            }
            $data      = $this->memberService->getWardrobe($member_id);
            return $this->showJson(200, $data);
        }


    }
