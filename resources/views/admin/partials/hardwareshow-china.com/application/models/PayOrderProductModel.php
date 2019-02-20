<?php
/**
 * Created by PhpStorm.
 * User: Chris_Chiang
 * Date: 2018/7/27
 * Time: 18:18
 */

class PayOrderProductModel extends Star_Model_Abstract
{
    protected $_name = 'pay_order_product';
    protected $_primary = 'id';

    public function getOrderProductsByOrderId($order_id){
        $sel=$this->select();
        $sel->from($this->getTableName() );
        $sel->where('order_id =?',$order_id)
            ->where('status >=?',0);
        return $this->fetchAll($sel);

    }


    public function getOrderProductByOrderProductIds($o_products){

            $res=array();
            foreach($o_products as $o_product){
                $sel=$this->select();
                $sel->from($this->getTableName('product'));
                $sel->where('product_id =?',$o_product)
                    ->where('status >=?',0);
                $res[]=$this->fetchRow($sel);
            }
//            print_r($res);
        return $res;

     }


     public function saveOrderProducts($pros){
        try{
            foreach($pros as $pro){
                $this->insert($pro);
            }
        }catch(Exception $e){
            $e->getMessage();
        }
     }
}