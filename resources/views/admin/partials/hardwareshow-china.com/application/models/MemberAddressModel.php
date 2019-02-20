<?php
/**
 * Created by PhpStorm.
 * User: Chris_Chiang
 * Date: 2018/7/27
 * Time: 18:12
 */

class MemberAddressModel extends Star_Model_Abstract
{
    protected $_name = 'member_address';
    protected $_primary = 'id';


    public function getMemberAddressByUid($member_id){

        $select = $this->select();
        $select->from($this->getTableName())
            ->where('member_id =?', $member_id)
            ->where('status >=?', 0);
        return $this->fetchAll($select);
    }

}
