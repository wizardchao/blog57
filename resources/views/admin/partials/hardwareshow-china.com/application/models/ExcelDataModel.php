<?php
/**
 * Created by PhpStorm.
 * User: Chris_Chiang
 * Date: 2018/7/25
 * Time: 16:59
 */
class ExcelDataModel extends Star_Model_Abstract
{

    protected $_name = 'excel';

    protected $_primary = 'data_id';


  public function getAll()
{
    $select = $this->select();
    $select->from($this->getTableName());
    return $this->fetchAll($select);
}

}