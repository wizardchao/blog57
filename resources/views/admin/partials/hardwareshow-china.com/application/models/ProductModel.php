<?php
/**
 * Created by PhpStorm.
 * User: Chris_Chiang
 * Date: 2018/7/20
 * Time: 10:10
 */
class ProductModel extends Star_Model_Abstract
{
    protected $_name = 'product';

    protected $_primary = 'product_id';


    public function getAllProductsCounts($param=array())
    {
        $select = $this->select();
        $select->from($this->getTableName(), "COUNT(1)")
            ->where('status >=?', 0);
        if ($param) {
            if (isset($param['name']) && $param['name']) {
                $select->where('name =?', $param['name']);
            }
            if (isset($param['category_id']) && $param['category_id']) {
                $select->where('category_id =?', $param['category_id']);
            }
        }
        return $this->fetchOne($select);
    }


    public function getProductsByPage($page, $page_size, Array $param){
        $select = $this->select();
        $select->from($this->getTableName())
            ->where('status >=?', 0);
        if ($param) {
            if (isset($param['name']) && $param['name']) {
                $select->where('name =?', $param['name']);
            }
            if (isset($param['category_id']) && $param['category_id']) {
                $select->where('category_id =?', $param['category_id']);
            }
        }

        if ($param['sort_flag']) {
            $select->limitPage($page, $page_size)->order(array( 'time_create DESC', 'news_id DESC'));
        } else {
            $select->limitPage($page, $page_size)->order(array( 'sort_id ASC', 'time_create DESC',));
        }

        return $this->fetchAll($select);
    }


    public function getProductByProductId($product_id){
        $select = $this->select();
        $select->from($this->getTableName())
            ->where('status >=?', 0)
            ->where('product_id =?',$product_id);
        return $this->fetchRow($select);
    }

}