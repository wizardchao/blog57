<?php


    class CrontabController extends Star_Controller_Action
    {
        protected $orderService;
        protected $goodsService;
        protected $dd;
        protected $backService;

        public function init()
        {
            parent::init();
            $this->orderService = new OrderService();
            $this->goodsService=new GoodsService();
            $this->dd=new Dd();
            $this->configService       = new ManageConfigService();
            $this->info                = $this->configService->getWechatConfig();
            $this->backService=new BackService();
        }

        /*
         * 每小时定时获取订单
         */
        public function indexAction()
        {
            $re         = 86400;
            $param      = array(
                'order_status' => 2,
                'cancel_type' => 0,
            );
            $order_list = $this->orderService->getAllOrderByOrderStatus($param);

            if (count($order_list)==0) {
                return $this->showJson(200);
            }
            $cur_time   = time();
            $edit_param = array(
                'time_cancel' => $cur_time,
                'time_update' => $cur_time,
                'order_status' => 1,
                'cancel_type' => 2,
            );
            foreach ($order_list as $rs) {
                if ($cur_time - $rs['time_create'] >= $re) {
                    $this->orderService->editOrderByOderNo($rs['order_no'], $edit_param);
                }
            }

            return $this->showJson(200);
        }


        //打乱商品顺序
        public function rand_sortAction()
        {
            $goods_list=$this->goodsService->getAllGoods();
            $min=1;
            $max=$num=count($goods_list);
            $rand_list=$this->dd->unique_rand($min, $max, $num);
            foreach ($goods_list as $key => $el) {
                $el_param=array(
              'rand_sort_id' => $rand_list[$key],
            );
                $this->goodsService->editGoods($el['goods_id'], $el_param);
            }
        }


        //发送异常提醒
        public function back_order_remindAction()
        {
            $curTime=time();
            $back_param=array(
            'order_status' => 5,
            'back_status' => 2,
            'time_back_remind' => $curTime- 7*24*3600,
          );

            $order_back_list=$this->orderService->getAllOrderByOrderStatus($param);

            if (empty($order_back_list)) {
                return $this->showJson(201);
            }
            foreach ($order_back_list as $el) {
                //发送提醒
                $this->orderService->editOrder($el['order_id'], array(
              'back_status' => 4,
              'time_update' => $curTime,
            ));
            }

            return $this->showJson(200);
        }



        //退货超过十天，状态自动改为已完成
        public function order_confirmAction()
        {
            $curTime=time();
            $back_param=array(
            'order_status' => 5,
            // 'back_status' => 2,
            // 'time_back_remind' => $curTime- 10*24*3600,
          );

            $order_back_list=$this->orderService->getAllOrderByOrderStatus($back_param);
            if (empty($order_back_list)) {
                return $this->showJson(201);
            }

            // $this->dd->dump($order_back_list);

            $refund_desc='申请超过十天，自动退款';
            foreach ($order_back_list as $el) {
                //发送提醒
                $this->orderService->editOrder($el['order_id'], array(
              'back_auto' => 1,
              'back_status' => 3,
              'time_update' => $curTime,
            ));

                // $totoal_fee=$el['total_price'];
                // $refund_fee=$el['row_total_price'];
                $total_fee=$refund_fee=1;
                //处理订单号
                $order_no=$el['order_no'];
                // $order_no=$dd->deal_order_no($el['order_no']);
                $re=$this->backService->backMsg($el['main_order_no'], $order_no, $refund_fee, $el['out_refund_no'], $total_fee, $refund_desc);
                if($re['status']==200){
                  $order_param=array(
                    'back_status' => 3,
                    'back_auto'=> 1,
                    'time_update' => time(),
                  );
                  $this->orderService->editOrder($el['id'], $order_param);
                }
            }

            return $this->showJson(200);
        }

        public function order_backAction()
        {
            $config_info      = $this->info;
            $PAYAPPID         = $config_info['app_id'];               // 小程序id
            $PAYMCHID         = $config_info['mch_id'];              // 支付商户id
            $PAYKEY           = $config_info['md5_key'];                // 商户的支付密钥
            $NOTIFY_URL       = $config_info['notify_url'];      // 微信服务器异步通知
            $app_cert_pem       = $config_info['app_cert_pem'];
            $app_key_pem       = $config_info['app_key_pem'];

            $wechatAppPay = new WechatAppBackService($PAYAPPID, $PAYMCHID, $NOTIFY_URL, $PAYKEY);

            $out_trade_no='181211161655115_1';

            //小程序的appid
            $param['appid'] = $PAYAPPID;
            //商户号
            $param['mch_id'] = $PAYMCHID;
            //随机字符串
            $nonce_str = $this->dd->GetRandStr(15);//随机数生成
          // echo $nonce_str,PHP_EOL;
            $param['nonce_str'] = $nonce_str;
            //商户订单号
            $param['out_trade_no'] = $out_trade_no;
            //商户退款单号
            $out_refund_no = $this->dd->GetRandStr(15);//生成随机数
            // echo $out_refund_no;
            $param['out_refund_no'] = $out_refund_no;

            //订单金额
            $param['total_fee'] = 1;
            //退款金额
            $param['refund_fee'] = 1;
            //退款原因
            $param['refund_desc'] = '商家自动退款';

            $stringSignTemp = $wechatAppPay->MakeSign($param);
            $param['sign'] = $stringSignTemp;
            $xml_data = $wechatAppPay->data_to_xml($param);

            $data = $this->dd->curl_post_ssl('https://api.mch.weixin.qq.com/secapi/pay/refund', $xml_data, $app_cert_pem, $app_key_pem);
//            '../../wxcertificate/apiclient_cert.pem','../../wxcertificate/apiclient_key.pem'
            $res = $wechatAppPay->xml_to_data($data);
            if ($res['result_code'] == 'SUCCESS') {//退款成功
                return $this->showJson(200);
            }
            print_r($res);
            exit;
        }
    }
