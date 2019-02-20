<?php
/**
 * Created by PhpStorm.
 * User: Chaos
 * Date: 2018/1/25
 * Time: 15:40
 */


class WebSetModel extends Star_Model_Abstract
{

    protected $_name = 'web_set';

    protected $_primary = 'id';

    protected $select;

    public function __construct()
    {
        parent::__construct();
        $select = $this->select();
        $this->select=$select->from($this->getTableName());
    }

    public function getSetInfoByName($name){

        $this->select->where('set_info =?',$name);
        return $this->fetchAll($this->select);

    }


}