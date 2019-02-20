<?php

/**
 * article_category Model
 *
 * @author QinYang Zhang
 */
class BrochureCategoryModel extends Star_Model_Abstract
{

    protected $_name = 'brochure_category';

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
            ->where('is_show = 1')
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

    public function getCategoryIdByKey($category_key)
    {
        $select = $this->select();
        $select->from($this->getTableName())
            ->where('category_key = ?', $category_key)
            ->where('status >=?', 0)
            ->where('is_show = 1');
        return $this->fetchCol($select);
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


    public function getInfo($parent_id)
    {
        $select = $this->select();
        $select->from($this->getTableName())
            ->where('category_id =?', $parent_id)
            ->where('is_show = 1')
            ->where('status >=?', 0);
        return $this->fetchRow($select);
    }


}

?>
