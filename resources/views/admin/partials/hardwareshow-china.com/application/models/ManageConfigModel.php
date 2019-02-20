<?php
    /**
     * Created by PhpStorm.
     * User: Chaos
     * Date: 2018/8/14
     * Time: 23:55
     */

    class ManageConfigModel extends Star_Model_Abstract
    {
        protected $_name = 'manage_config';
        protected $_primary = 'config_id';


        public function getConfigInfo($config_id)
        {
            $select = $this->select();
            $select->from($this->getTableName())
                ->where('config_id =?', $config_id);
            return $this->fetchRow($select);

        }


    }
