<?php
/**
 * Created by PhpStorm.
 * User: Chris_Chiang
 * Date: 2018/7/26
 * Time: 12:11
 */

/**
 * 订单类
 * 订单做了以下简化：
 * 创建订单时会检测库存量，但并不会预扣除库存量，因为这需要队列支持
 * 未支付的订单再次支付时可能会出现库存不足的情况
 * 所以，项目采用3次检测
 * 1. 创建订单时检测库存
 * 2. 支付前检测库存
 * 3. 支付成功后检测库存
 */
class OrderService
{
    protected $order_pros;
    protected $products;

    protected $uid;
    protected $order;
    protected $order_snap;
    protected $order_product;
    protected $mem_address;

    function __construct()
    {
        $this->order_snap=new PayOrderSnapModel();
        $this->order=new PayOrderModel();
        $this->order_product=new PayOrderProductModel();
        $this->mem_address=new MemberAddressModel();

    }

    /**
     * 生成订单表总逻辑，库存检测，生成快照，创建订单
     * @param $uid
     * @param $order_pros
     * @return array
     * @throws Exception
     */
    public function place($uid, $order_pros)
    {
        $this->order_pros = $order_pros;
        $this->products = $this->getProductsByOrder($order_pros);
        $this->uid = $uid;
        $status = $this->getOrderStatus();
        //print_r($status);
        if (!$status['pass']) {
            $status['order_id'] = -1;
            return $status;
        }

        $order_snap = $this->snapOrder();
//        print_r($order_snap);exit;
        $status = self::createOrderByTrans($order_snap);
        $status['pass'] = true;
        return $status;
    }

    /**
     * @param string $orderNo 订单号
     * @return array 订单商品状态
     * @throws Exception
     */
    public function checkOrderStock($order_id)
    {
        // 一定要从订单商品表中直接查询
        // 不能从商品表中查询订单商品
        // 这将导致被删除的商品无法查询出订单商品来
        $order_pros = $this->order_product->getOrderProductsByOrderId($order_id);
        $this->products = $this->getProductsByOrder($order_pros);
        $this->order_pros = $order_pros;
        $status = $this->getOrderStatus();
        return $status;
    }


    //发送消息方法；
    //    public function delivery($orderID, $jumpPage = '')
//    {
//        $order = OrderModel::where('id', '=', $orderID)
//            ->find();
//        if (!$order) {
//            throw new OrderException();
//        }
//        if ($order->status != OrderStatusEnum::PAID) {
//            throw new OrderException([
//                'msg' => '还没付款呢，想干嘛？或者你已经更新过订单了，不要再刷了',
//                'errorCode' => 80002,
//                'code' => 403
//            ]);
//        }
//        $order->status = OrderStatusEnum::DELIVERED;
//        $order->save();
////            ->update(['status' => OrderStatusEnum::DELIVERED]);
//        $message = new DeliveryMessage();
//        return $message->sendDeliveryMessage($order, $jumpPage);
//    }

    /**
     * 检测订单状态
     * @return array
     */
    private function getOrderStatus()
    {
        $status = [
            'pass' => true,
            'order_price' => 0,
            'p_status' => array(),
        ];
        foreach ($this->order_pros as $order_pro) {
            $p_status = $this->getProductStatus($order_pro['product_id'], $order_pro['count'], $this->products);
            if (!$p_status['have_stock']) {
                $status['pass'] = false;
            }
            $status['order_price'] += $p_status['total_price'];
            array_push($status['p_status'], $p_status);
        }
        return $status;
    }


    /**
     * 检验单个商品的状态
     * @param $order_pro_id
     * @param $o_count
     * @param $products
     * @return array|bool
     */
    private function getProductStatus($order_pro_id, $o_count, $products)
    {
        $p_index = -1;
        $p_status = [
            'id' => null,
            'have_stock' => false,
            'count' => 0,
            'name' => '',
            'total_price' => 0
        ];

        for ($i = 0; $i < count($products); $i++) {
            if ($order_pro_id == $products[$i]['product_id']) {
                $p_index = $i;
            }
        }

        if ($p_index == -1) {
            // 客户端传递的productid有可能根本不存在
           return array('error'=>'商品不存在');
        } else {
            $product = $products[$p_index];
            $p_status['id'] = $product['id'];
            $p_status['name'] = $product['name'];
            $p_status['count'] = $o_count;
            $p_status['total_price'] = $product['price'] * $o_count;

            if ($product['stock'] - $o_count >= 0) {
                $p_status['have_stock'] = true;
            }
        }
        return $p_status;
    }


    // 根据订单查找真实商品
    private function getProductsByOrder($o_products)
    {
        $order_pro_ids = array();
        foreach ($o_products as $item) {
            array_push($order_pro_ids, $item['product_id']);
        }
        // 为了避免循环查询数据库
        $products = $this->order_product->getOrderProductByOrderProductIds($order_pro_ids);
//        print_r($products);
        return $products;
    }

    /**
     * 获取会员下单默认地址
     * @return array
     */
    private function getUserAddress()
    {
        $user_address =  $this->mem_address->getMemberAddressByUid($this->uid);

        if (!$user_address) {
           return array();
        }else{
            if(count($user_address)>1){
                foreach($user_address as $add){
                    if($add['is_default']==1){
                        $address=$add;
                        return $address;
                    };
                }
            }else{
                return $user_address[0];
            }

        }

    }

    // 创建订单时没有预扣除库存量，简化处理
    // 如果预扣除了库存量需要队列支持，且需要使用锁机制
    private function createOrderByTrans($order_snap)
    {
        try {
            $orderNo = $this->makeOrderNo();
            $snap=$order=array();
            $order['member_id'] = $this->uid;
            $order['order_no'] = $orderNo;
            $order['total_price'] = $order_snap['order_price'];
            $order['total_count'] = $order_snap['total_count'];
            $order['time_create'] = time();
            //print_r($order);
            $order_id=$this->order->insert($order);
            //print_r($order_id);
            if($order_id){
                $snap['order_id'] = $order_id;
                $snap['order_no'] = $orderNo;
                $snap['snap_img'] = $order_snap['snap_img'];
                $snap['snap_name'] = $order_snap['snap_name'];
                $snap['snap_address'] = json_encode($order_snap['snap_address']);
                $snap['snap_items']= json_encode($order_snap['p_status']);
                $snap['time_create']= time();
                $snap_id=$this->order_snap->insert($snap);
                if(!$snap_id){
                    $snap_id=$this->order_snap->insert($snap);
                }
//                print_r($snap_id);
                $create_time = $order['time_create'];
                foreach ($this->order_pros as &$p) {
                    $p['pay_order_id'] = $order_id;
                    $p['member_id'] = $this->uid;
                    $p['quantity'] = $p['count'];
                    $p['time_create']=time();

                    unset($p['count']);
                }
//                           print_r($this->order_pros);
                $this->order_product->saveOrderProducts($this->order_pros);
                return array(
                    'order_no' => $orderNo,
                    'order_id' => $order_id,
                    'create_time' => $create_time
                );
            }



        } catch (Exception $ex) {
            $ex->getMessage();
        }
    }

    // 预检测并生成订单快照
    private function snapOrder()
    {
        // status可以单独定义一个类
        $snap = [
            'order_price' => 0,
            'total_count' => 0,
            'p_status' => [],
            'snap_address' => $this->getUserAddress(),
            'snap_name' => $this->products[0]['name'],
            'snap_img' => $this->products[0]['main_img_url'],
        ];

        if (count($this->products) > 1) {
            $snap['snap_name'] .= '等';
        }


        for ($i = 0; $i < count($this->products); $i++) {
            $product = $this->products[$i];
            $o_product = $this->order_pros[$i];

            $p_status = $this->snapProduct($product, $o_product['count']);
            $snap['order_price'] += $p_status['total_price'];
            $snap['total_count'] += $p_status['count'];
            array_push($snap['p_status'], $p_status);
        }
        return $snap;
    }

    // 单个商品库存检测
    private function snapProduct($product, $o_count)
    {
        $p_status = [
            'id' => null,
            'name' => null,
            'main_img_url'=>null,
            'count' => $o_count,
            'totalPrice' => 0,
            'price' => 0
        ];

        $p_status['counts'] = $o_count;
        // 以服务器价格为准，生成订单
        $p_status['total_price'] = $o_count * $product['price'];
        $p_status['name'] = $product['name'];
        $p_status['id'] = $product['id'];
        $p_status['main_img_url'] =$product['main_img_url'];
        $p_status['price'] = $product['price'];
        return $p_status;
    }

    /**
     * 生成订单号
     * @return string
     */
    public static function makeOrderNo()
    {

        $orderSn = intval(date('Y')). strtoupper(dechex(date('m'))) . date(
                'd') . substr(time(), -5) . substr(microtime(), 2, 5) . sprintf(
                '%02d', rand(0, 99));
        return $orderSn;
    }
}