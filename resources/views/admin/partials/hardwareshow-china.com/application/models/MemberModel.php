<?php
    /**
     * Created by PhpStorm.
     * User: Chaos
     * Date: 2018/7/6
     * Time: 14:41
     */

    class MemberModel  extends Star_Model_Abstract
    {
        protected $_name = 'member';
        protected $_primary = 'member_id';


        /*
         * 根据member_id返回结果
         */
        public function getMemberInfoById($member_id)
        {
            $select = $this->select();
            $select->from($this->getTableName())
                ->where('member_id =?', $member_id)
                ->where('status >=?', 1);
            return $this->fetchRow($select);
        }


        /**
         * 所有会员信息
         */
        public function getAllMemberInfo($param = null)
        {
            $select = $this->select();
            $select->from($this->getTableName())
                ->where('status >= ?', 0);
            if ($param) {
                if ($param['member_id']) {
                    $select->where('member_id =?', $param['member_id']);
                }
                if ($param['member_name']) {
                    $select->where('member_name LIKE ?', '%' . $param['member_name'] . '%');
                }
                if ($param['mobile']) {
                    $select->where('mobile =?', $param['mobile']);
                }

                if ($param['email']) {
                    $select->where('email =?', $param['email']);
                }

                if ($param['gender']) {
                    $select->where('gender =?', $param['gender']);
                }

            }
            return $this->fetchAll($select);
        }


        /**
         * 会员分页
         *
         * @param type $page , $page_size
         * @return type
         */
        public function getMemberInfoByPage($page, $page_size, $param = null)
        {
            $select = $this->select();
            $select->from($this->getTableName())
                ->where('status >= ?', 0);
            if ($param) {
                if ($param['member_id']) {
                    $select->where('member_id =?', $param['member_id']);
                }
                if ($param['member_name']) {
                    $select->where('member_name LIKE ?', '%' . $param['member_name'] . '%');
                }
                if ($param['mobile']) {
                    $select->where('member_mobile =?', $param['mobile']);
                }
                if ($param['email']) {
                    $select->where('member_email =?', $param['email']);
                }
                if ($param['gender']) {
                    $select->where('member_gender =?', $param['gender']);
                }
            }
            $select->order("time_reg DESC")->limitPage($page, $page_size);
            return $this->fetchAll($select);
        }


        /*
         *通过手机号获取会员信息
         *
         * @param type mobile
         * @return type
         */
        public function getMemInfoByMobile($mobile)
        {
            $select = $this->select();
            $select->from($this->getTableName())
                ->where('member_mobile =?', $mobile)
                ->where('status >=?', 1);
            return $this->fetchRow($select);
        }


        /*
        *通过邮箱账号获取会员信息
         * @parameter type email
         * @return type
        */
        public function getMemInfoByEmail($email)
        {
            $select = $this->select();
            $select->from($this->getTableName())
                ->where('member_email =?', $email)
                ->where('status >=?', 1);
            return $this->fetchRow($select);
        }


        /**取出总数
         * @param null $param
         * @return type
         */
        public function getAllCounts($param = NULL)
        {
            $select = $this->select();
            $select->from($this->getTableName() . " as m", "COUNT(1)")
                ->where('m.status >=?', 1);
            if ($param) {
                if ($param['member_id']) {
                    $select->where('member_id =?', $param['member_id']);
                }
                if ($param['member_name']) {
                    $select->where('member_name LIKE ?', '%' . $param['member_name'] . '%');
                }
                if ($param['mobile']) {
                    $select->where('member_mobile =?', $param['mobile']);
                }
                if ($param['email']) {
                    $select->where('member_email =?', $param['email']);
                }
                if ($param['gender']) {
                    $select->where('member_gender =?', $param['gender']);
                }

            }
            return $this->fetchOne($select);
        }


        /**通过openid获取会员信息
         * @param $openid
         * @return type
         */
        public function getMemberInfoByOpenid($openid)
        {
            $select = $this->select();
            $select->from($this->getTableName()." AS m")
                ->where('status >=?', 0)
                ->joinInner($this->getTableName('oauth_member_bind')." AS o",'m.member_id=o.member_id')
                ->where('o.openid =?', $openid);
            return $this->fetchRow($select);
        }


        /**
         * 判断手机号唯一
         */
        public function ck_mobile($mobile)
        {
            $select = $this->select();
            $select->from($this->getTableName(), "COUNT(1)")
                ->where('member_mobile =?', $mobile)
                ->where('status >=?', 1);
            return $this->fetchOne($select);
        }


        /**
         * 判断邮箱唯一
         */
        public function ck_email($email)
        {
            $select = $this->select();
            $select->from($this->getTableName(), "COUNT(1)")
                ->where('member_email =?', $email)
                ->where('status >=?', 1);
            return $this->fetchOne($select);
        }


    }