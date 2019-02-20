<?php
/**
 * Created by PhpStorm.
 * User: Chaos
 * Date: 2018/6/1
 * Time: 16:22
 */
require APPLICATION_PATH . '/modules/manage/controllers/CommonController.php';

class ExhibitorController extends CommonController
{
    public function init()
    {
        parent::init();
        $this->exhibitorService = new ExhibitorService();
    }


    /**
     * 展商列表
     */
    public function listAction()
    {
        $request = $this->getRequest();
        $page = (int)$request->getParam('page');
        $page_size = 15; //每页显示数
        $param = array();

        if ($request->isGet()) {
            $id = Star_String::escape($request->getParam('id'));
            $exhibitor_category_id = Star_String::escape($request->getParam('exhibitor_category_id'));
            $name = Star_String::escape($request->getParam('name'));
            $name_en = Star_String::escape($request->getParam('name_en'));
            $logo = Star_String::escape($request->getParam('logo'));
            $position = Star_String::escape($request->getParam('position'));

            $param = array(
                'id' => $id,
                'exhibitor_category_id' => $exhibitor_category_id,
                'name' => $name,
                'name_en' => $name_en,
                'logo' => $logo,
                'position' => $position,
                'tm_create' => time(),
                'tm_update' => time(),
                'status' => 1,
            );
        }

        $category_info = $this->exhibitorService->getAllCate();
        $getInfo = $this->exhibitorService->getInfoByPage($page, $page_size, $param);
        $list = $getInfo['list'];

        foreach($list as &$val){
            $exhibitorService = new ExhibitorService();
            $result = ($val['exhibitor_category_id']) ? $exhibitorService->getCategoryById($val['exhibitor_category_id']) : 0;
            $val['category_name'] = ($result) ? $result['category_name'] : " ";
        }
//        array_walk($list, function (&$val, $key, $param) {
//            $exhibitorService = new ExhibitorService();
//            $result = ($val['exhibitor_category_id']) ? $exhibitorService->getCategoryById($val['exhibitor_category_id']) : 0;
//            $val[$param['key']] = ($result) ? $result['category_name'] : " ";
//        }, array( 'key' => 'category_name' ));

        $page_info = $getInfo['page'];
        $this->view->assign(
            array(
                'category_info' => $category_info,
                'param' => $param,
                'list' => $list,
                'page' => $page_info,
                'cur_page' => $page,
            )
        );
        $this->render('list');
    }


    /**
     * 添加展商
     */
    public function addAction()
    {
        $request = $this->getRequest();
        if ($request->isPost()) {
            $exhibitor_category_id      = Star_String::escape($request->getParam('exhibitor_category_id'));
            $name       = Star_String::escape($request->getParam('name'));
            $name_en       = Star_String::escape($request->getParam('name_en'));
            $logo       = Star_String::escape($request->getParam('logo'));
            $position       = Star_String::escape($request->getParam('position'));
            $sort_id       = Star_String::escape($request->getParam('sort_id'));

            $param = array(
                'exhibitor_category_id' => (int)$exhibitor_category_id,
                'name' => $name,
                'name_en' => $name_en,
                'logo' => $logo,
                'position' => $position,
                'sort_id' => $sort_id,
                'tm_create' => time(),
                'tm_update' => time(),
                'status' => 1,
            );

            $info = $this->exhibitorService->add($param);
            if ($info) {
                return $this->showMessage('恭喜您，添加成功。', '/manage/exhibitor/list');
            } else {
                return $this->showWarning('对不起，添加失败。');
            }
        }

        $category_info = $this->exhibitorService->getAllCategoryInfo();
        $this->view->assign(
            array(
                'category_info' => $category_info,
                'param' => array(),
            )
        );
        $this->render('info');
    }


    /*
     * 编辑展商
     */
    public function editAction()
    {
        $request = $this->getRequest();
        $id = (int) $request->getParam('id');
        $page = (int) $request->getParam('cur_page');


        if ($request->isPost()) {
            $exhibitor_category_id = Star_String::escape($request->getParam('exhibitor_category_id'));
            $name = Star_String::escape($request->getParam('name'));
            $name_en = Star_String::escape($request->getParam('name_en'));
            $logo = Star_String::escape($request->getParam('logo'));
            $position = Star_String::escape($request->getParam('position'));
            $sort_id = Star_String::escape($request->getParam('sort_id'));

            $arr = array(
                'id' => $id,
            );
            $param = array(
                'exhibitor_category_id' => (int)$exhibitor_category_id,
                'name' => $name,
                'name_en' => $name_en,
                'logo' => $logo,
                'position' => $position,
                'sort_id' => $sort_id,
                'tm_update' => time(),
            );

            $exeInfo = $this->exhibitorService->update($arr, $param);
            if ($exeInfo) {
                return $this->showMessage('恭喜您，编辑成功。', '/manage/exhibitor/list');
            } else {
                return $this->showWarning('对不起，编辑失败。');
            }
        }

        $category_info = $this->exhibitorService->getAllCate();
        $info = $this->exhibitorService->getInfoById($id);
        $this->view->assign(
            array(
                'category_info' => $category_info,
                'param' => $info,
            )
        );
        $this->render('info');
    }


    /**
     * 删除展商
     */
    public function delAction()
    {
        $request = $this->getRequest();
        $id = (int)$request->getParam('id');
        $arr = array(
            'id' => $id,
        );
        $this->exhibitorService->del($arr);
        header("Location: /manage/exhibitor/list/");
    }


    /**
     * 分类列表
     */
    public function categoryAction()
    {
        $request   = $this->getRequest();
        $page      = (int)$request->getParam('page');  //        $category_name = Star_String::escape($request->getParam('category_name'));
        $page_size = 20;

        $category_data = $this->exhibitorService->getFirstCateByPage($page, $page_size);
        $this->exhibitorService->addICon($category_data['list']);
        $list = array( 'category_list' => $category_data['list'], );
        $this->view->assign($list);
        $this->render('cate_list');
    }


    /**
     * 添加分类
     */
    public function category_addAction()
    {
        $request = $this->getRequest();
        if ($request->isPost()) {
            $category_name = Star_String::escape($request->getParam('category_name'));
            $number        = (int)$request->getParam('number');
            $sort_id       = (int)$request->getParam('sort_id');
            $parent_id     = (int)$request->getParam('pid');

            if (empty($category_name)) {
                return $this->showWarning('分类名称不能为空');
            }

            $category_data = array(
                'category_name' => $category_name,
                'pid' => $parent_id,
                'sort_id' => $sort_id,
                'tm_create' => time(),
                'tm_update' => time(),
                'status' => 1,
            );
            $category_id   = $this->exhibitorService->insertCategory($category_data);
            if ($category_id) {
                if ($parent_id == 0) {
                    $relation = $category_id;
                    $level    = 1;
                } else {
                    $p_info   = $this->exhibitorService->getCategoryById($parent_id);
                    $relation = $p_info['relation'] . ',' . $category_id;
                    $level    = $p_info['level'] + 1;
                    unset($p_info);
                }
                $update_re = $this->exhibitorService->updateCategory($category_id, array( 'relation' => $relation, 'level' => $level, ));
                if ($update_re) {
                    return $this->showMessage('恭喜您，添加成功', '/manage/exhibitor/category');
                }
            } else {
                return $this->showWarning('很遗憾，添加失败');
            }
        }
        $category_info = $this->exhibitorService->getAllCate();

        //print_r($category_info);exit;
        //            echo "<pre>";
        //            print_r($category_info);
        //            echo "</pre>";exit;
        $this->view->assign('category_info', $category_info);
        $this->view->assign('category', array());
        $this->render('cate_info');
    }


    /**
     * 编辑分类
     */
    public function category_editAction()
    {
        $parent_info = $this->exhibitorService->getParentInfo();

        $request     = $this->getRequest();
        $exhibitor_category_id = (int)$request->getParam('exhibitor_category_id');
        $category    = $this->exhibitorService->getCategoryById($exhibitor_category_id);

        if (empty ($category)) {
            return $this->showWarning('分类不存在');
        }

        if ($request->isPost()) {
            $category_name = Star_String::escape($request->getParam('category_name'));
            $sort_id       = (int)$request->getParam('sort_id');
            $parent_id     = (int)$request->getParam('parent_id');

            if (empty($category_name)) {
                return $this->showWarning('分类名称不能为空');
            }

            if ($parent_id == $exhibitor_category_id) {
                return $this->showWarning('父类编号与子类编号不能相同！');
            }

            $category_data = array(
                'category_name' => $category_name,
                'sort_id' => $sort_id,
                'tm_update' => time(),
            );
            $rs            = $this->exhibitorService->updateCategory($exhibitor_category_id, $category_data);
            if ($rs) {
                return $this->showMessage('恭喜你，编辑成功', '/manage/exhibitor/category');
            } else {
                return $this->showWarning('很遗憾，编辑失败');
            }
        }
        $category_info = $this->exhibitorService->getAllCate();
        $this->view->assign('category_info', $category_info);
        $this->view->assign('parent_info', $parent_info);
        $this->view->assign('category', $category);
        $this->render('cate_info');
    }


    /**
     *删除分类
     */
    public function category_delAction()
    {
        $request     = $this->getRequest();
        $exhibitor_category_id = (int)$request->getParam('exhibitor_category_id');

        $category = $this->exhibitorService->getCategoryById($exhibitor_category_id);
        if (empty($category)) {
            return $this->showWarning('分类不存在');
        }

        $rs = $this->exhibitorService->deleteCategory($exhibitor_category_id);
        if ($rs) {
            return $this->showMessage('删除成功');
        } else {
            return $this->showWarning('删除失败');
        }
    }


}
