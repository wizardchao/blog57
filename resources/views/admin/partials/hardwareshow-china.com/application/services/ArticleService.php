<?php
/*
* author: chason
* date: 2018-08-13
*/
class ArticleService
{
    protected $articleModel;
    protected $articleChildModel;
    protected $articleCategoryModel;


    /*
    * 构造函数
    */
    public function __construct()
    {
        $this->articleModel    = new ArticleModel();
        $this->articleChildModel = new ArticleChildModel();
        $this->articleCategoryModel = new ArticleCategoryModel();
    }


    /*
     * 活动分类列表
     */
    public function getCategoryList($params = array())
    {
        return $this->articleCategoryModel->getAll($params);
    }

    /*
     * 通过id获取
     */
    public function getCategoryById($id)
    {
        return $this->articleCategoryModel->getById($id);
    }


    /*
     * 活动分类添加
     */
    public function addCategory($param)
    {
        return $this->articleCategoryModel->insert($param);
    }


    /*
     * 活动分类编辑
     */
    public function updateCategory($arr, $param)
    {
        return $this->articleCategoryModel->update($arr, $param);
    }


    /*
     * 活动分类删除
     */
    public function delCategory($arr)
    {
        $data = array(
            'status' => -1,
        );
        return $this->articleCategoryModel->update($arr, $data);
    }


    /*
     * 活动列表分页
     */
    public function getArticlePage($page, $page_size, $param)
    {
        $total     = $this->articleModel->getAllCounts($param);
        $page      = Star_Page::setPage($page, $page_size, $total);
        $list      = $this->articleModel->getList($page, $page_size, $param);
        $page_info = compact('page', 'page_size', 'total');
        $page_data = Star_Page::show($page_info);
        return array( 'page' => $page_data, 'total' => $total, 'list' => $list );
    }


    /*
     * 通过id获取
     */
    public function getArticleById($id)
    {
        return $this->articleModel->getById($id);
    }

    /*
     * 活动列表
     */
    public function getArticleList($params)
    {
        return $this->articleModel->getAll($params);
    }

    /*
     * 活动添加
     */
    public function addArticle($param)
    {
        return $this->articleModel->insert($param);
    }


    /*
     * 活动编辑
     */
    public function updateArticle($arr, $param)
    {
        return $this->articleModel->update($arr, $param);
    }


    /*
     * 活动删除
     */
    public function delArticle($arr)
    {
        $data = array(
            'status' => -1,
        );
        return $this->articleModel->update($arr, $data);
    }


    /*
     * 活动子活动列表分页
     */
    public function getArticleChildPage($page, $page_size, $param)
    {
        $total     = $this->articleChildModel->getAllCounts($param);
        $page      = Star_Page::setPage($page, $page_size, $total);
        $list      = $this->articleChildModel->getList($page, $page_size, $param);
        $page_info = compact('page', 'page_size', 'total');
        $page_data = Star_Page::show($page_info);
        return array( 'page' => $page_data, 'total' => $total, 'list' => $list );
    }

    /*
     * 通过id获取
     */
    public function getArticleChildById($id)
    {
        return $this->articleChildModel->getById($id);
    }


    /*
     * 活动子活动列表
     */
    public function getArticleChildList($params)
    {
        return $this->articleChildModel->getAll($params);
    }

    /*
     * 活动子活动添加
     */
    public function addArticleChild($param)
    {
        return $this->articleChildModel->insert($param);
    }


    /*
     * 活动子活动编辑
     */
    public function updateArticleChild($arr, $param)
    {
        return $this->articleChildModel->update($arr, $param);
    }


    /*
     * 活动子活动删除
     */
    public function delArticleChild($arr)
    {
        $data = array(
            'status' => -1,
        );
        return $this->articleChildModel->update($arr, $data);
    }


    public function getAllCategoryList(){
        $list=$this->getCategoryList();

        foreach($list as &$item){
            $item['child']=$this->articleModel->getChild($item['article_category_id']);
        }

        return $list;

    }


    public function getCategoryByTemplate($template_name){
        return $this->articleCategoryModel->getCategoryByTemplate($template_name);
    }

    public function getArticleByTemplate($template_name){
        return $this->articleModel->getArticleByTemplate($template_name);
    }


}