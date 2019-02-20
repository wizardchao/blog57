<?php
/*
* author: chason
* date: 2018-08-13
*/
class ActivityChildModel extends Star_Model_Abstract
{

    protected $_name = 'activity_child';
    protected $_primary = 'activity_child_id';


    /**
     * 通过id获取信息
     * @param $about_id
     * @return type
     */
    public function getById($id)
    {
        $select = $this->select();
        $select->from($this->getTableName())
            ->where('activity_child_id =?', $id)
            ->where('status >=?', 1);
        return $this->fetchRow($select);
    }


    /**
     * 返回有关信息
     *
     * @param type $page
     * @param type $page_size
     * @param type $params
     * @return type
     */
    public function getList($page, $page_size, Array $params)
    {
        $select = $this->select();
        $select->from($this->getTableName())
            ->where('status >=?', 1);

        if (isset($params['activity_id']) && $params['activity_id']) {
            $select->where('activity_id =?', $params['activity_id']);
        }

        $select->limitPage($page, $page_size)->order('sort_id asc', 'activity_child_id DESC');
        return $this->fetchAll($select);
    }


    /**
     * 返回所有信息
     */
    public function getAll($params)
    {
        $select = $this->select();
        $select->from($this->getTableName())
            ->where('status >=?', 1);

        if (isset($params['activity_id']) && $params['activity_id']) {
            $select->where('activity_id =?', $params['activity_id']);
        }

        $select->order("sort_id asc", "activity_child_id DESC");
        return $this->fetchAll($select);
    }


    /*
    * 取出总数
     */
    public function getAllCounts($param = NULL)
    {
        $select = $this->select();
        $select->from($this->getTableName(), "COUNT(1)")
            ->where('status >=?', 1);
        if (isset($param['activity_id']) && $param['activity_id']) {
            $select->where('activity_id =?', $param['activity_id']);
        }
        return $this->fetchOne($select);
    }


}