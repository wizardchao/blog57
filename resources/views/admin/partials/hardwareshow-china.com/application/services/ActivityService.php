<?php
/*
* author: chason
* date: 2018-08-13
*/
class ActivityService
{
    protected $activityModel;
    protected $activityChildModel;
    protected $activityCategoryModel;


    /*
    * 构造函数
    */
    public function __construct()
    {
        $this->activityModel    = new ActivityModel();
        $this->activityChildModel = new ActivityChildModel();
        $this->activityCategoryModel = new ActivityCategoryModel();
    }


    /*
     * 活动分类列表
     */
    public function getCategoryList($params = array())
    {
        return $this->activityCategoryModel->getAll($params);
    }

    /*
     * 通过id获取
     */
    public function getCategoryById($id)
    {
        return $this->activityCategoryModel->getById($id);
    }


    /*
     * 活动分类添加
     */
    public function addCategory($param)
    {
        return $this->activityCategoryModel->insert($param);
    }


    /*
     * 活动分类编辑
     */
    public function updateCategory($arr, $param)
    {
        return $this->activityCategoryModel->update($arr, $param);
    }


    /*
     * 活动分类删除
     */
    public function delCategory($arr)
    {
        $data = array(
            'status' => -1,
        );
        return $this->activityCategoryModel->update($arr, $data);
    }


    /*
     * 活动列表分页
     */
    public function getActivityPage($page, $page_size, $param)
    {
        $total     = $this->activityModel->getAllCounts($param);
        $page      = Star_Page::setPage($page, $page_size, $total);
        $list      = $this->activityModel->getList($page, $page_size, $param);
        $page_info = compact('page', 'page_size', 'total');
        $page_data = Star_Page::show($page_info);
        return array( 'page' => $page_data, 'total' => $total, 'list' => $list );
    }


    /*
     * 通过id获取
     */
    public function getActivityById($id)
    {
        return $this->activityModel->getById($id);
    }

    /*
     * 活动列表
     */
    public function getActivityList()
    {
        return $this->activityModel->getAll();
    }

    /*
     * 活动添加
     */
    public function addActivity($param)
    {
        return $this->activityModel->insert($param);
    }


    /*
     * 活动编辑
     */
    public function updateActivity($arr, $param)
    {
        return $this->activityModel->update($arr, $param);
    }


    /*
     * 活动删除
     */
    public function delActivity($arr)
    {
        $data = array(
            'status' => -1,
        );
        return $this->activityModel->update($arr, $data);
    }


    /*
     * 活动子活动列表分页
     */
    public function getActivityChildPage($page, $page_size, $param)
    {
        $total     = $this->activityChildModel->getAllCounts($param);
        $page      = Star_Page::setPage($page, $page_size, $total);
        $list      = $this->activityChildModel->getList($page, $page_size, $param);
        $page_info = compact('page', 'page_size', 'total');
        $page_data = Star_Page::show($page_info);
        return array( 'page' => $page_data, 'total' => $total, 'list' => $list );
    }

    /*
     * 通过id获取
     */
    public function getActivityChildById($id)
    {
        return $this->activityChildModel->getById($id);
    }


    /*
     * 活动子活动列表
     */
    public function getActivityChildList($params)
    {
        return $this->activityChildModel->getAll($params);
    }

    /*
     * 活动子活动添加
     */
    public function addActivityChild($param)
    {
        return $this->activityChildModel->insert($param);
    }


    /*
     * 活动子活动编辑
     */
    public function updateActivityChild($arr, $param)
    {
        return $this->activityChildModel->update($arr, $param);
    }


    /*
     * 活动子活动删除
     */
    public function delActivityChild($arr)
    {
        $data = array(
            'status' => -1,
        );
        return $this->activityChildModel->update($arr, $data);
    }


    public function getAllCategoryList(){
        $list=$this->getCategoryList();

        foreach($list as &$item){
            $item['child']=$this->activityModel->getChild($item['activity_category_id']);
        }

        return $list;

    }


    public function getActivityByTemplate($template_name){
        return $this->activityModel->getActivityByTemplate($template_name);
    }


}