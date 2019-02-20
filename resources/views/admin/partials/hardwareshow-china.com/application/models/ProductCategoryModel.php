<?php
/**
 * Created by PhpStorm.
 * User: Chris_Chiang
 * Date: 2018/7/20
 * Time: 10:09
 */
class ProductCategoryModel extends Star_Model_Abstract
{
    protected $_name = 'product_category';
    protected $_primary = 'category_id';

    public function getCategoryByPage($page, $page_size, array $params)
    {
        $select = $this->select();
        $select->from($this->getTableName())
            ->where('status >=?', 0)
            ->limitPage($page, $page_size)
            ->order('sort_id ASC');

        if (isset($params['category_name']) && $params['category_name']) {
            $select->where('category_name = ?', $params['category_name']);
        }
        return $this->fetchAll($select);
    }



    public function getAllCate()
    {
        $select = $this->select();
        $select->from($this->getTableName())
            ->where('status >=?', 0)
            ->order('sort_id ASC','category_id ASC');

        return $this->fetchAll($select);
    }



    public function getSonCateByPid($pid,$sort_id_order='asc',$id_order='desc')
    {
        $select = $this->select();
        $select->from($this->getTableName())
            ->where('status >=?', 0)
            ->where("pid = ?", $pid);

        $select->order(array( "sort_id $sort_id_order", "category_id $id_order" ));


        $menu_list=$this->fetchAll($select);
        return $menu_list;
    }


    public function getCategoryCount(array $params)
    {
        $select = $this->select();
        $select->from($this->getTableName(), 'count(1)')
            ->where('status >=?', 0);

        if (isset($params['category_name']) && $params['category_name']) {
            $select->where('category_name = ?', $params['category_name']);
        }
        if (isset($params['level']) && $params['level']) {
            $select->where('level = ?', $params['level']);
        }
        return $this->fetchOne($select);
    }



    public function getAllCategory()
    {
        $select = $this->select();
        $select->from($this->getTableName())
//            ->where('is_show = 1')
            ->where('status >=?', 0)
            ->order('sort_id ASC');
        return $this->fetchAll($select);
    }


    public function getCategoryByKey($category_key)
    {
        $select = $this->select();
        $select->from($this->getTableName())
            ->where('category_key = ?', $category_key)
            ->where('is_show = 1')
            ->where('status >=?', 0)
            ->order('sort_id ASC');
        return $this->fetchAll($select);
    }

    public function getParentInfo()
    {
        $select = $this->select();
        $select->from($this->getTableName())
            ->where('is_show = 1')
            ->where('status >=?', 0)
            ->order('sort_id ASC');
        return $this->fetchAll($select);
    }

}