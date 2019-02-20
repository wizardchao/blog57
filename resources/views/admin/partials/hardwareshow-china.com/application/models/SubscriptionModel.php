<?php
    /**
     * Created by PhpStorm.
     * User: Chaos
     * Date: 2018/8/15
     * Time: 13:14
     */

    class SubscriptionModel extends Star_Model_Abstract
    {
        protected $_name = 'subscription';

        protected $_primary = 'subscription_id';


        /*
   * 根据subscription_id返回结果
   */
        public function getSubscriptionInfoById($subscription_id)
        {
            $select = $this->select();
            $select->from($this->getTableName())
                ->where('subscription_id =?', $subscription_id)
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
        public function getSubscriptionInfoByPage($page, $page_size, Array $param)
        {
            $select = $this->select();
            $select->from($this->getTableName())
                ->where('status >=?', 1);
            if ($param) {
                if (isset($param['subscription_id']) && $param['subscription_id']) {
                    $select->where('subscription_id =?', $param['subscription_id']);
                }

                if (isset($param['subscription_email']) && $param['subscription_email']) {
                    $select->where('subscription_email like ?', '%'.$param['subscription_email'].'%');
                }

                if (isset($param['subscription_name']) && $param['subscription_name']) {
                    $select->where('subscription_name like ?', '%'.$param['subscription_name'].'%');
                }
            }
            $select->limitPage($page, $page_size)->order(array( 'time_create desc' ));
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
                if (isset($param['subscription_email']) && $param['subscription_email']) {
                    $select->where('subscription_email like ?', '%'.$param['subscription_email'].'%');
                }

                if (isset($param['subscription_name']) && $param['subscription_name']) {
                    $select->where('subscription_name like ?', '%'.$param['subscription_name'].'%');
                }
            }
            return $this->fetchOne($select);
        }


        public function getCountByEmail($email)
        {
            $select = $this->select();
            $select->from($this->getTableName(), "COUNT(1)")
                ->where('subscription_email =?', $email)
                ->where('status >=?', 1);
            return $this->fetchOne($select);
        }
    }