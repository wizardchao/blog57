<?php
class ManageAdminLogModel extends Star_Model_Abstract
{
    protected $_name = 'manage_admin_log';
    protected $_primary = 'log_id';


    /*
     * 根据id返回结果
     */
    public function getInfoById($log_id)
    {
        $select = $this->select();
        $select->from($this->getTableName())
            ->where('log_id =?', $log_id)
            ->where('status >=?', 1);
        return $this->fetchRow($select);
    }


    /**
     * 返回有关信息
     *
     * @param type $page
     * @param type $page_size
     * @return type
     */
    public function getInfoByPage($page, $page_size, Array $param)
    {

        $select = $this->select();
        $select->from($this->getTableName()." AS m")
            ->where('m.status >=?', 1);
        if ($param) {
            if (isset($param['log_id']) && $param['log_id']) {
                $select->where('m.log_id = ?', $param['log_id']);
            }
            if (isset($param['id']) && $param['id']) {
                $select->where('m.id = ?', $param['id']);
            }
        }
        $select->joinLeft($this->getTableName("manage_admin") . " AS d", "m.id = d.id", array("username"))
        ->order(array('m.log_id desc'))->limitPage($page, $page_size);
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
        if ($param) {
            if (isset($param['log_id']) && $param['log_id']) {
                $select->where('log_id = ?', $param['log_id']);
            }
            if (isset($param['id']) && $param['id']) {
                $select->where('id = ?', $param['id']);
            }
        }

        return $this->fetchOne($select);
    }


}