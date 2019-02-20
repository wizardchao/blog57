<?php
/*
* author: chason
* date: 2018-08-13
*/
class ActivityCategoryModel extends Star_Model_Abstract
{

    protected $_name = 'activity_category';
    protected $_primary = 'activity_category_id';


    /**
     * 通过id获取信息
     * @param $about_id
     * @return type
     */
    public function getById($id)
    {
        $select = $this->select();
        $select->from($this->getTableName())
            ->where('activity_category_id =?', $id)
            ->where('status >=?', 1);
        return $this->fetchRow($select);
    }


    /**
     * 返回所有信息
     */
    public function getAll()
    {
        $select = $this->select();
        $select->from($this->getTableName())
            ->where('status >=?', 1)
            ->order("sort_id asc");
        return $this->fetchAll($select);
    }


}
