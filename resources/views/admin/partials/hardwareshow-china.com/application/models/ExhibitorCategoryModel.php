<?php

/**
 * article_category Model
 *
 * @author QinYang Zhang
 */
class ExhibitorCategoryModel extends Star_Model_Abstract
{

    protected $_name = 'exhibitor_category';

    protected $_primary = 'exhibitor_category_id';

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
            ->order('sort_id ASC','exhibitor_category_id DESC');
        return $this->fetchAll($select);
    }



    public function getSonCateByPid($pid,$sort_id_order='asc',$id_order='desc')
    {
        $select = $this->select();
        $select->from($this->getTableName())
            ->where('status >=?', 0)
            ->where("pid = ?", $pid);

        $select->order(array( "sort_id $sort_id_order", "exhibitor_category_id $id_order" ));


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
            ->where('status >=?', 0)
            ->order('sort_id ASC');
        return $this->fetchAll($select);
    }

    public function getCategoryIdByKey($category_key)
    {
        $select = $this->select();
        $select->from($this->getTableName())
            ->where('status >=?', 0);
        return $this->fetchCol($select);
    }



    public function getInfo($parent_id)
    {
        $select = $this->select();
        $select->from($this->getTableName())
            ->where('exhibitor_category_id =?', $parent_id)
            ->where('status >=?', 0);
        return $this->fetchRow($select);
    }


    public function getParentInfo()
    {
        $select = $this->select();
        $select->from($this->getTableName())
            ->where('status >=?', 0)
            ->order('sort_id ASC');
        return $this->fetchAll($select);
    }


    public function getCategoryByPid($pid){
      $select = $this->select();
      $select->from($this->getTableName())
          ->where('status >=?', 0)
          ->where('pid =?', $pid)
          ->order('sort_id ASC');
          // echo $select;exit;
      return $this->fetchAll($select);
    }


}
