<?php
    /**
     * Created by PhpStorm.
     * User: Chaos
     * Date: 2018/9/22
     * Time: 14:47
     */

    class WechatpayController extends Star_Controller_Action
    {
        protected $dd;
        protected $orderService;
        protected $memberService;

        public function init()
        {
            parent::init();
            $this->dd            = new Dd();
            $this->orderService  = new OrderService();
            $this->memberService = new MemberService();
        }


        public function notifyAction()
        {
            $xml = file_get_contents('php://input', 'r');
            //将服务器返回的XML数据转化为数组
            $data = $this->dd->toArray($xml);
            if (($data['return_code'] == 'SUCCESS') && ($data['result_code'] == 'SUCCESS')) {
                //获取服务器返回的数据
                $order_sn       = $data['out_trade_no'];          // 订单单号
                $openid         = $data['openid'];                  // 付款人openID
                $total_fee      = ($data['total_fee']) / 100;            // 付款金额
                $transaction_id = $data['transaction_id'];  // 微信支付流水号

                $member_id = $this->memberService->getMemberIdByOpenid($openid);
                if (empty($member_id)) {
                    $str = '<xml><return_code><![CDATA[FAIL]]></return_code><return_msg><![CDATA[签名失败]]></return_msg></xml>';
                    echo $str;
                    exit;
                }
                $order_info = $this->orderService->getOrderInfoByNotify($order_sn, $member_id, $total_fee, $transaction_id);
                if ($order_info) {
                    $str = '<xml><return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA[OK]]></return_msg></xml>';
                } else {
                    $str = '<xml><return_code><![CDATA[FAIL]]></return_code><return_msg><![CDATA[签名失败]]></return_msg></xml>';
                }
                echo $str;
                return $order_info;
            }
        }
    }