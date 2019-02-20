<?php
    /**
     * Created by PhpStorm.
     * User: Chaos
     * Date: 2018/6/8
     * Time: 10:30
     */

    class MemberService
    {
        protected $memberModel;
        protected $oauth_member;

        /*
         * 构造函数
         */
        public function __construct()
        {
            $this->memberModel = new MemberModel();
            $this->oauth_member= new OauthMemberBindModel();
        }


        /** 会员列表
         * @param $page
         * @param $page_size
         * @param $param
         * @return array
         */
        public function memberList($page, $page_size, $param)
        {
            $total     = $this->memberModel->getAllCounts($param);
            $page      = Star_Page::setPage($page, $page_size, $total);
            $list      = $this->memberModel->getMemberInfoByPage($page, $page_size, $param);
            $page_info = compact('page', 'page_size', 'total');
            $page_data = Star_Page::show($page_info);
            return array( 'page' => $page_data, 'total' => $total, 'list' => $list );
        }


        /**根据member_id获取会员信息
         * @param $member_id
         * @return type
         */
        public function getMemberInfo($member_id)
        {
            return $this->memberModel->getMemberInfoById($member_id);
        }


        /**
         * 添加成员
         */
        public function insertMember($param)
        {
            return $this->memberModel->insert($param);
        }


        /**
         * 添加微信信息
         * @param $info
         * @return type
         */
        public function saveMemberFromWx($info){
            $data=array('open_id'=>$info['open_id'],);
            //待完成，还有一些字段没有插入
            return $this->oauth_member->insert($data);
        }



        /**
         * 删除会员
         */
        public function delMember($member_id){
            $where=array(
                'member_id' => $member_id,
            );

            $param=array(
                'status' => -1,
            );
            return $this->memberModel->update($where, $param);
        }

        /**编辑会员信息
         * @param $member_id
         * @param $param
         * @return int
         */
        public function editMember($member_id, $param)
        {
            $where = array(
                'member_id' => $member_id,
            );
            return $this->memberModel->update($where, $param);
        }


        /**根据openid修改信息
         * @param $openid
         * @param $param
         * @return int
         */
        public function updateMemberByopenid($openid, $param)
        {
            $where = array(
                'openid' => $openid,
            );
            return $this->memberModel->update($where, $param);
        }

        /**检验openid
         * @param $openid
         * @return int
         */
        public function ckOpenid($openid)
        {
            $info = $this->getMemberInfoByOpenid($openid);
            if (empty($info)) {
                return 0;
            }
            return 1;
        }


        /**通过openid获取用户信息
         * @param $openid
         * @return type
         */
        public function getMemberInfoByOpenid($openid)
        {
            return $this->memberModel->getMemberInfoByOpenid($openid);
        }


        /**
         * 解密数据
         */
        public function encryptData($session_key, $iv, $userdata)
        {
            $pc      = new WXBizDataCrypt(APPID, $session_key);
            $errCode = $pc->decryptData($userdata, $iv, $data);
            if ($errCode == 0) {
                return json_decode($data, true);
            } else {
                print($errCode . "\n");
            }
            exit;
        }





        /**
         * 判断手机号码唯一性
         */
        public function ck_mobile($mobile)
        {
            $count = $this->memberModel->ck_mobile($mobile);
            if ($count) {
                return 1;
            }
            return 0;
        }


        /**
         * 判断邮箱唯一性
         */
        public function ck_email($email)
        {
            $count = $this->memberModel->ck_email($email);
            if ($count) {
                return 1;
            }
            return 0;
        }


        /**
         * 获取设备名
         */
        public function getDeviceNameById($device_id)
        {
            $device_arr = $this->getDeviceList();
            return ($device_arr[$device_id]) ?: '其他';
        }


        /**返回设备表
         * @return array
         */
        public function getDeviceList()
        {
            return array(
                1 => 'PC',
                2 => '移动端',
                3 => '微信',
                4 => '后台',
            );
        }

    }