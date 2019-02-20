<?php
/**
 * Created by PhpStorm.
 * User: Chaos
 * Date: 2018/1/25
 * Time: 15:40
 */


class BrochureModel extends Star_Model_Abstract
{
    protected $_name = 'brochure';

    protected $_primary = 'brochure_id';

    /*
     * 根据brochure_id返回结果
     */
    public function getBrochureInfoById($brochure_id)
    {
        $select = $this->select();
        $select->from($this->getTableName())
            ->where('brochure_id =?', $brochure_id)
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
    public function getBrochureInfoByPage($page, $page_size, array $param)
    {
        $select = $this->select();
        $select->from($this->getTableName())
            ->where('status >=?', 1);
        if ($param) {
            if (isset($param['category_id']) && $param['category_id']) {
                $select->where('category_id =?', $param['category_id']);
            }
            if (isset($param['brochure_id']) && $param['brochure_id']) {
                $select->where('brochure_id =?', $param['brochure_id']);
            }
            if (isset($param['brochure_title']) && $param['brochure_title']) {
                $select->where('brochure_title =?', $param['brochure_title']);
            }
            if (isset($param['is_recommend']) && is_numeric($param['is_recommend'])) {
                $select->where('is_recommend =?', $param['is_recommend']);
            }
            if (isset($param['home_show']) && is_numeric($param['home_show'])) {
                $select->where('home_show =?', $param['home_show']);
            }
        }
        $select->limitPage($page, $page_size)->order(array('sort_id ASC', 'time_update desc'));
        return $this->fetchAll($select);
    }


    /*
    * 取出总数
    */
    public function getAllCounts($param = null)
    {
        $select = $this->select();
        $select->from($this->getTableName(), "COUNT(1)")
            ->where('status >=?', 1);
        if ($param) {
            if (isset($param['category_id']) && $param['category_id']) {
                $select->where('category_id =?', $param['category_id']);
            }
            if (isset($param['brochure_id']) && $param['brochure_id']) {
                $select->where('brochure_id =?', $param['brochure_id']);
            }
            if (isset($param['brochure_title']) && $param['brochure_title']) {
                $select->where('brochure_title =?', $param['brochure_title']);
            }
            if (isset($param['is_recommend']) && is_numeric($param['is_recommend'])) {
                $select->where('is_recommend =?', $param['is_recommend']);
            }
            if (isset($param['home_show']) && is_numeric($param['home_show'])) {
                $select->where('home_show =?', $param['home_show']);
            }
        }
        return $this->fetchOne($select);
    }


    public function getAllBrochure($param=array())
    {
        $select = $this->select();
        $select->from($this->getTableName())
          ->where('status >=?', 1);
        return $this->fetchAll($select);
    }
}
