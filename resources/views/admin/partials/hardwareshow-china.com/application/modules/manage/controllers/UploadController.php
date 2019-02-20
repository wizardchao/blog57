<?php

    class UploadController extends Star_Controller_Action
    {

        protected $upload;
        public function init()
        {
            parent::init();
            $this->disableLayout();
            $this->upload=new ImageUpload();
        }


        public function indexAction()
        {
            $imgDomain = substr(DOMAIN_FILE, 2);
            $request   = $this->getRequest();
            $backinput = $request->getParam('backinput'); // 上传框
            $backview  = $request->getParam('backview'); // 预览地址

            if ($request->isPost()) {
                if ($_FILES['userfile']['size'] > 5000000) {
                    echo '<script>alert("文件超过5000000KB");history.back();</script>';
                    exit;
                }

                if ($_FILES['userfile']['name'] != '') {
                    // 创建上传目录
                    //                if (@mkdir(APPLICATION_PATH . '/../' . $imgDomain . '/' . date('Ym'))) {
                    //                }
                    if (@mkdir(APPLICATION_PATH . '/../file/' . date('Ym'))) {
                    }

                    $files     = explode('.', $_FILES['userfile']['name']);
                    $file_name = date('mdHis');
                    $file_exp  = $files[count($files) - 1];
                    $name      = $file_name . '.' . $file_exp;

                    $save_path = APPLICATION_PATH . '/../file/' . date('Ym') . '/' . $name;
                    $result    = move_uploaded_file($_FILES["userfile"]["tmp_name"], $save_path);
                    if ($result) {
                        echo "
                    <script>
                        parent.document.getElementById('" . $backinput . "').value='/" . date('Ym') . '/' . $name . "';
                        parent.document.getElementById('" . $backview . "').src='//" . $imgDomain . '/' . date('Ym') . '/' . $name . "';
                        location.replace('/manage/upload/index/?backinput=" . $backinput . "&backview=" . $backview . "');
                    </script>
                    ";
                    }
                }
            }

            $this->view->assign(array( 'param' => array( 'backinput' => $backinput, 'backview' => $backview, ) ));
            $this->render('upload');
        }



        public function  upload_localAction($filename='file'){
            $file=$_FILES[$filename];
            $res=$this->upload->setImageBasePath('../img')
                ->setServerName(DOMAIN_FILE)
                ->upload($file);
            if($res['error']==0){
                $this->showJson(200,$res['data']['image_url']);
            }else{
                $this->showJson(304,$res['message']);
            }


        }


        public function fileAction()
        {
            $imgDomain = substr(DOMAIN_IMG, 2);
            $request   = $this->getRequest();
            $backinput = $request->getParam('backinput'); // 上传框

            if ($request->isPost()) {
                if ($_FILES['userfile']['size'] > 500000000) {
                    echo '<script>alert("文件超过50MB");history.back();</script>';
                    exit;
                }

                if ($_FILES['userfile']['name'] != '') {
                    // 创建上传目录
                    //                if (@mkdir(APPLICATION_PATH . '/../' . $imgDomain . '/' . date('Ym'))) {
                    //                }
                    if (@mkdir(APPLICATION_PATH . '/../file/' . date('Ym'))) {
                    }

                    $files     = explode('.', $_FILES['userfile']['name']);
                    $file_name = date('mdHis');
                    $file_exp  = $files[count($files) - 1];
                    $name      = $file_name . '.' . $file_exp;

                    $save_path = APPLICATION_PATH . '/../file/' . date('Ym') . '/' . $name;
                    $result    = move_uploaded_file($_FILES["userfile"]["tmp_name"], $save_path);
                    if ($result) {
                        echo "
                    <script>
                        parent.document.getElementById('" . $backinput . "').value='/" . date('Ym') . '/' . $name . "';
                        location.replace('/manage/upload/file/?backinput=" . $backinput . "');
                    </script>
                    ";
                    }
                }
            }

            $this->view->assign(array( 'param' => array( 'backinput' => $backinput, ) ));
            $this->render('uploadfile');
        }


    }
