<?php
/**
 * Created by PhpStorm.
 * User: Chris_Chiang
 * Date: 2018/7/20
 * Time: 11:14
 */
require APPLICATION_PATH . '/modules/manage/controllers/CommonController.php';

class ProductController extends CommonController
{

    protected $productService;
    protected $page_size;
    protected $property_service;

    public function init()
    {
        parent::init();
        $this->page_size = 3;
        $this->productService = new ProductService();


    }


    /**
     * 分类列表
     */
    public function cate_listAction()
    {
        $request = $this->getRequest();
        $page = (int)$request->getParam('page');
        $page_size = $this->page_size;
        $category_data = $this->productService->getFirstCateByPage($page, $page_size);
        $this->productService->addIcon($category_data['list']);
        $this->view->assign($category_data);
        $this->render('cate_list');
    }


    /**
     * 添加分类
     */
    public function cate_addAction()
    {
        $request = $this->getRequest();
        if ($request->isPost()) {
            $category_name = Star_String::escape($request->getParam('category_name'));
            $number = (int)$request->getParam('number');
            $is_show = (int)$request->getParam('is_show');
            $parent_id = (int)$request->getParam('pid');

            if (empty($category_name)) {
                return $this->showWarning('分类名称不能为空');
            }

            $category_data = array(
                'category_name' => $category_name,
                'pid' => $parent_id,
                'number' => $number,
                'is_show' => $is_show,
                'add_time' => time(),
            );
            $category_id = $this->productService->insertCategory($category_data);
            if ($category_id) {
                if ($parent_id == 0) {
                    $relation = $category_id;
                    $level = 1;
                } else {
                    $p_info = $this->productService->getCategoryById($parent_id);
                    $relation = $p_info['relation'] . ',' . $category_id;
                    $level = $p_info['level'] + 1;
                    unset($p_info);
                }
                $update_re = $this->productService->updateCategory($category_id, array('relation' => $relation, 'level' => $level,));
                if ($update_re) {
                    return $this->showMessage('恭喜您，添加成功', '/manage/product/cate_list');

                }
            } else {
                return $this->showWarning('很遗憾，添加失败');
            }
        }
        $category_info = $this->productService->getAllCate();
        $this->view->assign('category_info', $category_info);
        $this->view->assign('category', array());
        $this->render('cate_info');
    }


    /**
     * 编辑分类
     */
    public function cate_editAction()
    {
        $parent_info = $this->productService->getParentInfo();

        $request = $this->getRequest();
        $category_id = (int)$request->getParam('category_id');
        $category = $this->productService->getCategoryById($category_id);

        if (empty ($category)) {
            return $this->showWarning('分类不存在');
        }

        if ($request->isPost()) {
            $category_name = Star_String::escape($request->getParam('category_name'));
            $category_key = Star_String::escape($request->getParam('category_key'));
            $number = (int)$request->getParam('number');
            $sort_id = (int)$request->getParam('sort_id');
            $is_show = (int)$request->getParam('is_show');
            $parent_id = (int)$request->getParam('parent_id');

            if (empty($category_name)) {
                return $this->showWarning('分类名称不能为空');
            }

            if ($parent_id == $category_id) {
                return $this->showWarning('父类编号与子类编号不能相同！');
            }

            $category_data = array(
                'category_name' => $category_name,
                'category_key' => $category_key,
                //                    'parent_id' => $parent_id,
                //                    'number' => $number,
                'sort_id' => $sort_id,
                'is_show' => $is_show,
                'add_time' => time(),
            );
            $rs = $this->productService->updateCategory($category_id, $category_data);
            if ($rs) {
                return $this->showMessage('恭喜你，编辑成功', '/manage/product/cate_list');
            } else {
                return $this->showWarning('很遗憾，编辑失败');
            }
        }
        $category_info = $this->productService->getAllCate();
        $this->view->assign('category_info', $category_info);
        $this->view->assign('parent_info', $parent_info);
        $this->view->assign('category', $category);
        $this->render('cate_info');
    }


    /**
     *删除分类
     */
    public function cate_delAction()
    {
        $request = $this->getRequest();
        $category_id = (int)$request->getParam('category_id');

        $category = $this->productService->getCategoryById($category_id);
        if (empty($category)) {
            return $this->showWarning('分类不存在');
        }

        $rs = $this->productService->deleteCategory($category_id);
        if ($rs) {
            return $this->showMessage('删除成功');
        } else {
            return $this->showWarning('删除失败');
        }
    }

    /**
     * 分类排序
     * @return type
     */
    public function cate_orderAction()
    {
        $request = $this->getRequest();
        $sorts = $request->getParams();
        foreach ($sorts as $k => $v) {
            $me = explode('_', $k);
            $id = intval($me[2]);

            $param = array('sort_id' => intval($v),);
            $new_id['category_id'] = $id;
            $this->productService->updateCategory($new_id, $param);
        }

        $gourl = $_SERVER['HTTP_REFERER'];
        return $this->showMessage('排序已经成功修改。', $gourl);


    }


    /**
     * 产品列表
     */
    public function pro_listAction()
    {
        $request = $this->getRequest();
        $page = (int)$request->getParam('page');
        $page_size = 15; //每页显示数
        if ($request->isGet()) {
            $data = $request->getParams();
            $param = array();
            foreach ($data as $k => $v) {
                $param[$k] = Star_String::escape($v);
            }
        $category_info = $this->productService->getAllCate();
        $pro_info     = $this->productService->getProductsByPage($page, $page_size, $param);
        //print_r($pro_info);exit;

        $this->view->assign(
            array(
                'category_info' => $category_info,
                'param' => $param,
                'pro_list' => $pro_info['list'],
                'page' =>  $pro_info['page'],
                'cur_page' => $page,
            ));
        $this->render('list');

        }
    }


        /**
         * 添加产品
         * @return typet
         */
         public function pro_addAction()
            {
                $request = $this->getRequest();
                if ($request->isPost()) {
                    $data = $request->getParams();
                    //转实体
                    $new_data = array();
                    foreach ($data as $k => $v) {
                        $new_data[$k] = Star_String::escape($v);
                    }
                    if (empty($new_data['name'])) {
                        return $this->showWarning('标题不能为空！');
                    }
                    $news_info = $this->productService->saveProduct($new_data);
                    if ($news_info) {
                        return $this->showMessage('恭喜您，添加产品成功。', '/manage/product/pro_list');
                    } else {
                        return $this->showWarning('对不起，产品添加失败。');
                    }
                } else {
                    if ($request->isGet()) {
                        $param = array();
                        $category_info = $this->productService->getAllCate();

                        $this->view->assign(
                            array(
                               'category_info' => $category_info,
                                'param' => $param,
                            ));
                        $this->render('pro_main');
                    }


                }
            }


    /**
     * 编辑产品
     * @return type
     */
    public function  pro_editAction(){
        //$this->pro_banner_imgAction();
        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getParams();
            //print_r($data);exit;
            //转实体
            $new_data = array();
            foreach ($data as $k => $v) {
                if(!is_array($v)){
                    $new_data[$k] = Star_String::escape($v);
                }else{
                    $new_data[$k]=$v;
                }
            }
            if (empty($new_data['name'])) {
                return $this->showWarning('产品名称不能为空！');
            }
            $news_info = $this->productService->saveDetail($new_data);
            if ($news_info) {
                return $this->showMessage('恭喜您，产品编辑成功。', $_SERVER['HTTP_REFERER']);
            } else {
                return $this->showWarning('对不起，产品编辑失败。');
            }
        } else {
            if ($request->isGet()) {

                $data = $request->getParams();
                //面包屑链接处理
                if(!$data){
                    $this->redirect($_SERVER['HTTP_REFERER']);
                }
                $category_info = $this->productService->getAllCate();
                $properties= $this->productService->getAllProperties($data);
                if(empty($properties)){
                    $properties= $this->productService->getAllProperties();
                }
                //print_r($properties);exit;
                $detail=$proper=array();
                foreach($properties as $prop){
                    if($prop['product_id']==0){
                        $proper[$prop['detail']]='';
                    }else if($prop['name']=="商品详情"){
                       $detail=$prop['detail'];
                    }else{
                            $proper[$prop['name']]=$prop['detail'];
                    }

                    }

                }
                 $pro=$this->productService->getProductDetailByProductId($data['product_id']);
//                print_r($pro);exit;
                $this->view->assign(
                    array(
                        'category_info' => $category_info,
                        'product' => $pro,
                        'property'=>$proper,
                        'detail'=>empty($detail)?'':$detail,

                    ));
                $this->render('pro_detail');
            }



    }


        /**
         * 更改产品状态
         */
        public function pro_update_statusAction(){
            $request = $this->getRequest();
            if($request->isGet()){
                $status=$request->getParams();
                $res=$this->productService->changeProductStatus($status);
                if($res){
                    $this->redirect($_SERVER['HTTP_REFERER']);
                }
            }

        }


        /**
         * 产品属性列表
         */
        public function property_listAction(){

            $res=$this->productService->getAllProperties();
            $this->view->assign(
                array(
                'property_list'=>$res,
                ));
            $this->render('property');
        }


        /**
         * 产品属性添加
         */
        public function property_addAction()
        {

            $request = $this->getRequest();
            if ($request->isPost()) {
                $data = $request->getParams();
                //转实体
                $new_data = array();
                foreach ($data as $k => $v) {
                    $new_data[$k] = Star_String::escape($v);
                }
                $res=$this->productService->saveProperty($new_data);
                if($res){
                    $this->redirect('property_list');
                }
            }else{
                $assign['property']=array();
                $this->view->assign($assign);
                $this->render('property_info');
            }
        }


        /**
         * 产品属性编辑
         */
        public function property_editAction(){
            $request = $this->getRequest();
            if ($request->isGet()) {
                $data = $request->getParams();
                //转实体
                $new_data = array();
                foreach ($data as $k => $v) {
                    $new_data[$k] = Star_String::escape($v);
                }

                $res=$this->productService->getPropertyByPropertyId($new_data);
                $assign['property']=$res;
                $this->view->assign($assign);
                $this->render('property_info');
            }else if($request->isPost()){
                $data = $request->getParams();
                //转实体
                $new_data = array();
                foreach ($data as $k => $v) {
                    $new_data[$k] = Star_String::escape($v);
                }
                $res=$this->productService->updateProperty($new_data);
                //print_r($res);exit;
                if($res){
                    $this->redirect('property_list');
                }else{
                    $this->showWarning('您未做任何修改！');
                }
            }

        }


    /**
     * 产品属性删除
     * @return type
     */
    public  function property_delAction(){
        $request = $this->getRequest();
            if ($request->isGet()) {
                $data = $request->getParams();
                //转实体
                $new_data = array();
                foreach ($data as $k => $v) {
                    $new_data[$k] = Star_String::escape($v);
                }
                $res=$this->productService->delProperty($new_data);
                if($res){
                    return $this->redirect('property_list');
                }
            }
    }

    /**
     * 获取产品banner图
     */
    public  function pro_banner_img_getAction(){
        $request = $this->getRequest();
        $pro['product_id']= $request->getParam('product_id');
        $res=$this->productService->getBannerImgs($pro);
        $this->showJson(200,$res);

    }

    public function pro_banner_img_delAction(){
        $request = $this->getRequest();
        $img_id = $request->getParam('id');
        if($request->isAjax()){
            $data=array(
                'img_id'=>$img_id
            );
            $res=$this->productService->delBannerImgByImgId($data);
            if($res){
                $this->showJson(200,$res);
            }

        }

    }

}

