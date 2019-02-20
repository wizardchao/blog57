<?php
    /**
     * Created by PhpStorm.
     * User: Chaos
     * Date: 2018/7/6
     * Time: 15:04
     */
    require APPLICATION_PATH . '/modules/manage/controllers/CommonController.php';

    class MemberController extends CommonController
    {
        protected $memberService;
        protected $page_size;

        public function init()
        {
            parent::init();
            $this->memberService = new MemberService();
            $this->manageService = new ManageService();
            $this->page_size     = 20;
        }


        /**
         * 会员列表
         */
        public function member_listAction()
        {
            $request   = $this->getRequest();
            $page      = (int)$request->getParam('page');
            $member_id = Star_String::escape($request->getParam('member_id'));
            $member_name= Star_String::escape($request->getParam('member_name'));

            $param = array(
                'member_id' => $member_id,
                'member_name' => $member_name,
            );

            $member_info = $this->memberService->memberList($page, $this->page_size, $param);

            $member_list = $member_info['list'];
            $page        = $member_info['page'];

            foreach ($member_list as &$list) {
                $list['avatar']     = ($list['member_avatar']) ?$list['member_avatar']: $list['wechat_avatar'];
                $list['device_reg'] = $this->memberService->getDeviceNameById($list['device_reg']);
                $list['member_gender']=($list['member_gender']==1)?'男':'女';
            }
            $data = array(
                'member_list' => $member_list,
                'page' => $page,
                'param' => $param,
            );
            //                        $this->dump($data);
            $this->view->assign($data);
            $this->render('member_list');
        }


        /**
         * 添加会员
         */
        public function member_addAction()
        {
            $request = $this->getRequest();
            if ($request->isPost()) {
                $member_name   = Star_String::escape($request->getParam('member_name'));
                $member_gender = (int)Star_String::escape($request->getParam('member_gender'));
                $member_mobile = Star_String::escape($request->getParam('member_mobile'));
                $member_email  = Star_String::escape($request->getParam('member_email'));
                $device_reg    = (int)Star_String::escape($request->getParam('device_reg'));
                $password      = Star_String::escape($request->getParam('member_password'));
                if (empty($member_name)) {
                    return $this->showWarning('会员姓名不能为空！');
                }

                if (empty($member_email) && empty($member_mobile)) {
                    return $this->showWarning('手机号码和邮箱不能都为空！');
                }

                if ($member_mobile) {
                    if (!Star_String::isMobile($member_mobile)) {
                        return $this->showWarning('手机号码格式有误！');
                    }

                    $ck_mobile = $this->memberService->ck_mobile($member_mobile);
                    if ($ck_mobile) {
                      return $this->showWarning('手机号码重复！');
                    }
                    unset($ck_mobile);
                }

                if ($member_email) {
                    if (!Star_String::isEmail($member_email)) {
                        return $this->showWarning('邮箱格式有误！');
                    }

                    $ck_email = $this->memberService->ck_email($member_email);
                    if ($ck_email) {
                        return $this->showWarning('邮箱重复！');
                    }
                    unset($ck_email);

                }

                if (empty($password)) {
                    return $this->showWarning('密码不能为空！');
                }

                $param = array(
                    'member_name' => $member_name,
                    'member_mobile' => $member_mobile,
                    'member_email' => $member_email,
                    'member_gender' => $member_gender,
                    'member_password' => $this->manageService->encrytPassword($password),
                    'time_reg' => time(),
                    'ip_reg' => $_SERVER["REMOTE_ADDR"],
                    'device_reg' => ($device_reg) ?$device_reg: 4,
                    'status' => 1,
                );
                //            $this->dump($param);
                $re = $this->memberService->insertMember($param);
                unset($param);
                if ($re) {
                    return $this->showMessage('恭喜您，添加会员成功。', '/manage/member/member_list');
                } else {
                    return $this->showWarning('对不起，添加会员失败。');
                }
            }

            $param       = array(
                'device_reg' => 4,
            );
            $device_list = $this->memberService->getDeviceList();

            $this->view->assign(
                array(
                    'device_list' => $device_list,
                    'param' => $param,
                ));
            unset($device_list);
            unset($param);
            $this->render('member_info');
        }


        /**
         * 编辑会员
         */
        public function member_editAction()
        {
            $request   = $this->getRequest();
            $member_id = (int)Star_String::escape($request->getParam('id'));
            if (empty($member_id)) {
                return $this->showWarning('会员编号不能为空');
            }
            $member_info = $this->memberService->getMemberInfo($member_id);
            if (empty($member_info)) {
                return $this->showWarning('会员信息为空');
            }
            if ($request->isPost()) {
                $member_name   = Star_String::escape($request->getParam('member_name'));
                $member_gender = (int)Star_String::escape($request->getParam('member_gender'));
                $member_mobile = Star_String::escape($request->getParam('member_mobile'));
                $member_email  = Star_String::escape($request->getParam('member_email'));
                $password      = Star_String::escape($request->getParam('member_password'));

                if (empty($member_name)) {
                    return $this->showWarning('会员姓名不能为空！');
                }

                if (empty($member_email) && empty($member_mobile)) {
                    return $this->showWarning('手机号码和邮箱不能都为空！');
                }

                if ($member_mobile) {
                    if (!Star_String::isMobile($member_mobile)) {
                        return $this->showWarning('手机号码格式有误！');
                    }

                    $ck_mobile = $this->memberService->ck_mobile($member_mobile);
                    if ($ck_mobile) {
                        if ($member_info['member_mobile'] != $member_mobile) {
                            return $this->showWarning('手机号码重复！');
                        }

                    }
                    unset($ck_mobile);
                }

                if ($member_email) {
                    if (!Star_String::isEmail($member_email)) {
                        return $this->showWarning('邮箱格式有误！');
                    }

                    $ck_email = $this->memberService->ck_email($member_email);
                    if ($ck_email) {
                        if ($member_info['member_email'] != $member_email) {
                            return $this->showWarning('邮箱重复！');
                        }

                    }
                    unset($ck_email);
                }

                $param = array(
                    'member_name' => $member_name,
                    'member_mobile' => $member_mobile,
                    'member_email' => $member_email,
                    'member_gender' => $member_gender,
                );
                if ($password) {
                    $param['member_password'] = $this->manageService->encrytPassword($password);
                }

                $re = $this->memberService->editMember($member_id, $param);
                unset($param);
                if ($re) {
                    return $this->showMessage('恭喜您，修改会员成功。', '/manage/member/member_list');
                } else {
                    return $this->showWarning('对不起，修改会员失败。');
                }
            }

            $this->view->assign(
                array(
                    'param' => $member_info,
                ));
            unset($member_info);
            unset($member_id);
            $this->render('member_info');
        }


        /**
         * 删除会员
         */
        public function member_delAction()
        {
            $request   = $this->getRequest();
            $member_id = (int)Star_String::escape($request->getParam('id'));
            if (empty($member_id)) {
                return $this->showWarning('会员编号不能为空');
            }

            $member_info = $this->memberService->getMemberInfo($member_id);
            if (empty($member_info)) {
                return $this->showWarning('会员信息为空');
            }

            unset($member_info);
            $re=$this->memberService->delMember($member_id);
            if ($re) {
                unset($member_id);
                return $this->showMessage('恭喜您，删除会员成功。', '/manage/member/member_list');
            } else {
                return $this->showWarning('对不起，删除会员失败。');
            }
        }
    }