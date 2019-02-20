<?php
require APPLICATION_PATH.'/modules/manage/controllers/CommonController.php';

class IndexController extends CommonController
{


    public function init()
    {
        parent::init();

        $this->manageService = new ManageService();
    }


    public function loginAction()
    {
       $res= $this->manageService->loginCheck();
        if(!$res){
            $this->render('login');
        }else{
            header('Location:/manage');
        }

    }


    /*
    * 检验登录
    */
    public function logincheckAction()
    {
        $req = $this->getRequest();
        $username = $req->getParam('login_username');
        $password = $req->getParam('login_password');
        if( $req->isPost() ){
            $result = $this->manageService->loginAdmin($username, $password);

            switch($result){
                case 200:
                    header('Location: /manage');
                    break;

                case 501:
                    return $this->showWarning('该用户名不存在！');
                    break;

                case 502:
                    return $this->showWarning('该账户异常！');
                    break;

                case 503:
                    return $this->showWarning('密码错误！');
                    break;
            }
        }
    }


    /*
    * 退出
    */
    public function logoutAction()
    {
        $this->manageService->logout();
        header('Location: /manage');
    }


    /*
     * 后台首页
     */
    public function indexAction()
    {
        $this->render('index');
    }

}
