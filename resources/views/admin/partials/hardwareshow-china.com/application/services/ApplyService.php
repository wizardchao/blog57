<?php
/**
 * Created by PhpStorm.
 * User: Chaos
 * Date: 2018/1/25
 * Time: 15:48
 */
class ApplyService
{
    protected $applyExhibitionModel;
    protected $applyAudienceModel;


    /*
    * 构造函数
    */
    public function __construct()
    {
        $this->applyExhibitionModel = new ApplyExhibitionModel();
        $this->applyAudienceModel = new ApplyAudienceModel();
    }


    /**
     * 展商预定: 返回分页数据
     *
     * @return type
     */
    public function getExhibitionByPage($page, $page_size, $param)
    {
        $total     = $this->applyExhibitionModel->getAllCounts($param);
        $page      = Star_Page::setPage($page, $page_size, $total);
        $list      = $this->applyExhibitionModel->getList($page, $page_size, $param);
        $page_info = compact('page', 'page_size', 'total');
        $page_data = Star_Page::show($page_info);
        return array( 'page' => $page_data, 'total' => $total, 'list' => $list );
    }


    /*
     * 展商预定: 查找
     */
    public function getExhibitionById($id)
    {
        return $this->applyExhibitionModel->getById($id);
    }


    /*
     * 展商预定: 添加数据
     */
    public function insertExhibition($param)
    {
        return $this->applyExhibitionModel->insert($param);
    }


    /*
     * 展商预定: 编辑
     */
    public function updateExhibition($arr, $param)
    {
        return $this->applyExhibitionModel->update($arr, $param);
    }


    /*
     * 展商预定: 删除
     */

    public function delExhibition($arr)
    {
        $data = array(
            'status' => -1,
        );
        return $this->applyExhibitionModel->update($arr, $data);
    }





    /**
     * 观众登记: 返回分页数据
     *
     * @return type
     */
    public function getAudienceByPage($page, $page_size, $param)
    {
        $total     = $this->applyAudienceModel->getAllCounts($param);
        $page      = Star_Page::setPage($page, $page_size, $total);
        $list      = $this->applyAudienceModel->getList($page, $page_size, $param);
        $page_info = compact('page', 'page_size', 'total');
        $page_data = Star_Page::show($page_info);
        return array( 'page' => $page_data, 'total' => $total, 'list' => $list );
    }


    /*
     * 观众登记: 查找
     */
    public function getAudienceById($id)
    {
        return $this->applyAudienceModel->getById($id);
    }
    

    /*
     * 观众登记: 添加数据
     */
    public function insertAudience($param)
    {
        return $this->applyAudienceModel->insert($param);
    }


    /*
     * 观众登记: 编辑
     */
    public function updateAudience($arr, $param)
    {
        return $this->applyAudienceModel->update($arr, $param);
    }


    /*
     * 观众登记: 删除
     */

    public function delAudience($arr)
    {
        $data = array(
            'status' => -1,
        );
        return $this->applyAudienceModel->update($arr, $data);
    }


}