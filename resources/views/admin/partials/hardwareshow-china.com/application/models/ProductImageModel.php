<?php
/**
 * Created by PhpStorm.
 * User: Chris_Chiang
 * Date: 2018/7/24
 * Time: 14:43
 */
class ProductImageModel extends Star_Model_Abstract
{
    protected $_name = 'product_image';
    protected $_primary = 'product_image_id';

    public function getProductBannerImgsByProductId($product_id){
        $select = $this->select();
        $select->from($this->getTableName('image') ." AS i")
            ->where('i.status >=?', 0)
            ->joinInner($this->getTableName() ." AS b","i.img_id = b.img_id")
            ->where('b.status >=?', 0)
            ->where('b.product_id =?',$product_id);
        return $this->fetchAll($select);
    }



}