<?php
/**
 * Created by PhpStorm.
 * User: Chris_Chiang
 * Date: 2018/7/23
 * Time: 11:31
 */
class ProductPropertyModel extends Star_Model_Abstract
{
    protected $_name = 'product_property';

    protected $_primary = 'property_id';

    /**
     * 获取所有产品的属性值
     */
    public function getPropertiesByProductId($pid)
    {
        $select = $this->select();
        $select->from($this->getTableName())
            ->where('status >=?', 0)
            ->where('product_id =?',$pid);
        return $this->fetchAll($select);
    }


    public function getPropertyByPropertyId($property_id){
        $select = $this->select();
        $select->from($this->getTableName())
            ->where('status >=?', 0)
            ->where('property_id =?',$property_id);
        return $this->fetchRow($select);
    }


}