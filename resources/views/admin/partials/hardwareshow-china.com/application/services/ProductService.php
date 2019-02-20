<?php
/**
 * Created by PhpStorm.
 * User: Chris_Chiang
 * Date: 2018/7/20
 * Time: 10:17
 */
class ProductService{
    protected $pro_model;
    protected $pro_cate_model;
    protected $PropertyModel;
    protected $util;
    protected $image;
    protected $product_image;
    public $tree;

    /**
     * 初始化model对象
     * ProductService constructor.
     */
    public function __construct()
    {
        $this->pro_model = new ProductModel();
        $this->pro_cate_model = new ProductCategoryModel();
        $this->PropertyModel=new ProductPropertyModel();
        $this->image=new ImageModel();
        $this->product_image=new ProductImageModel();
        $this->tree= new TreeService();


        $this->util=new UtilsHelper();

    }




        /**
         * 根据条件返回分类列表
         * @param int $page
         * @param int $page_size
         * @param array $params
         * @return array
         */
        public function getCategoryByPage($page, $page_size, array $params)
    {
        $total         = $this->pro_cate_model->getCategoryCount($params);
        $page          = Star_Page::setPage($page, $page_size, $total);
        $page_info     = array(
            'page' => $page,
            'page_size' => $page_size,
            'total' => $total,
        );
        $page_data     = Star_Page::show($page_info);
        $category_list = $this->pro_cate_model->getCategoryByPage($page, $page_size, $params);
        return array(
            'page' => $page_data,
            'category_list' => $category_list,
            'total' => $total,
        );
    }


    /**
     * 获取无限极分类列表，通过一级菜单分页取出对应分类列表，并返回
     * @param int $page
     * @param int $page_size
     * @return array
     */
    public function getFirstCateByPage($page=1, $page_size=5)
    {
        $res=$this->getCateTreeInCache();
        $total=count($res);
        $page = Star_Page::setPage($page, $page_size, $total);

        if(is_array($res)){
            $start=$page-1;
            $lenth=$page_size;
            $list=array_slice($res,$start*$lenth,$lenth);
            $page_info = compact('page', 'page_size', 'total');
            $page_data = Star_Page::show($page_info);
            return array( 'page' => $page_data, 'total' => $total, 'list' => $list );
        }
        return array();

    }

    /**
     * 减少查询cpu的IO开销，将分类信息的数据存入缓存，并且形成树形结构
     * @return array|type
     */
    public function getCateTreeInCache(){

        $back_menu=Star_Cache::get('product_cate');
        if(!empty($back_menu)){
            return $back_menu;
        }else{
            $res=$this->pro_cate_model->getAllCate();
            $res=$this->tree->generateTree($res,'category_id','pid');
            Star_Cache::set(DOMAIN_MANAGE.'product_cate',$res);
            return $res;
        }

    }


    /**
         * @param $menu_list
         * @return mixed
         * 加icon样式
         */
        public function addIcon(&$menu_list){
            foreach ($menu_list as &$list) {

                switch ($list['level']) {
                    case 1:
                        $label_class   = '';
                        $list['class'] = "fz14 fwb";
                        break;
                    case 2:
                        $label_class   = '<i class="level-label">—</i>';
                        $list['class'] = "fz14";
                        break;
                    default:
                        $level         = $list['level'] - 2;
                        $list['class'] = '';
                        $label_class   = str_repeat('<i class="level-label"></i>', $level) . '<i class="level-label">—</i>';

                }
                $list['category_name'] = $label_class . $list['category_name'];
                unset($label_class);
                if($list['son_cate']){
                    self::addIcon($list['son_cate']);
                }
            }
            return $menu_list;
        }




         /**
         * 添加分类
         * @param array $data
         * @return int
         */
        public function insertCategory($data)
        {
            Star_Cache::set(DOMAIN_MANAGE.'product_cate','');
            return $this->pro_cate_model->insert($data);
        }



        /**
         * 更新分类
         * @param string $where
         * @param array $data
         * @return int
         */
        public function updateCategory($where, $data)
        {

            Star_Cache::set(DOMAIN_MANAGE.'product_cate','');
            return $this->pro_cate_model->update($where, $data);
        }


    /**
         * 删除分类
         * @param string $where
         * @return int
         */
        public function deleteCategory($news_cate_id)
        {
            $where = "relation LIKE " . "'%{$news_cate_id}%'";
            $param = array(
                'status' => -1,
            );
            Star_Cache::set(DOMAIN_MANAGE.'product_cate','');
            return $this->pro_cate_model->update($where, $param);

        }

        /**
         * 根据ID返回分类信息
         * @param int $category_id
         * @return array
         */
        public function getCategoryById($category_id)
        {
            return $this->pro_cate_model->getPk($category_id);
        }

        public function getAllCategoryInfo()
        {
            return $this->pro_cate_model->getAllCategoryInfo();
        }

        public function getParentInfo()
        {
            return $this->pro_cate_model->getParentInfo();
        }


    /**
     * 添加select的Icon信息
     * @param $menuLists
     * @return mixed
     */
        public function addSelectIcon(&$menuLists){
            foreach ($menuLists as &$item) {
                if ($item['level'] > 1) {
                    $level             = $item['level'] - 1;
                    $item['category_name'] = str_repeat('&nbsp;&nbsp;&nbsp;', $level) ."|—". $item['category_name'];
                }
                if(isset($item['son_cate'])){
                    self::addSelectIcon($item['son_cate']);
                }
            }

            return $menuLists;
        }


    /**
     * 得到所有分类信息
     * @return type
     */
        public function getAllCate(){
            $category_list = $this->getCateTreeInCache();
            if($category_list){
                $this->addSelectIcon($category_list);
            }
            return $category_list;
        }


        public function saveProduct($param)
        {


            $img['url']=$param['main_img_url'];
            $img['time_create']=time();
            $img_id=$this->image->insert($img);


            $param    = array(
                'name' =>$param['name'],
                'product_id' =>intval($param['product_id']),
                'price' =>$param['price'],
                'main_img_url'=>$param['main_img_url'],
                'stock' => $param['stock'],
                'summary'=>$param['summary'],
                'category_id' =>intval($param['category_id']),
                'img_id'=>isset($img_id)?intval($img_id):'',
            );

            return $this->pro_model->insert($param);
        }


        public function getProductsByPage($page, $page_size, $new_data)
        {
            $param = array(
                'name' => $new_data['name'],
                'product_id' =>intval($new_data['product_id']),
                'price' => $new_data['price'],
                'stock' =>$new_data['stock'],
                'category_id' => intval($new_data['category_id']),
            );

            $total     = $this->pro_model->getAllProductsCounts($param);
            $page=Star_Page::setPage($page, $page_size, $total);
            $list      = $this->pro_model->getProductsByPage($page, $page_size, $param);
            //取出category_name的值
            foreach($list as &$li){
                $cate_info=$this->getCategoryById($li['category_id']);
                $li['category_name']=$cate_info['category_name'];
                unset($li['category_id']);
            }
            $page_info = compact('page', 'page_size', 'total');
            $page_data = Star_Page::show($page_info);
            return array( 'page' => $page_data, 'total' => $total, 'list' => $list );
        }


    /**
     * 通过产品id获得产品详情
     */
        public function getProductDetailByProductId($product_id){
            return $this->pro_model->getProductByProductId($product_id);
        }


        public function saveProperty($data){

            $data=array(
                'name'=>$data['name'],
                'detail'=>$data['detail'],
                'status'=>1,
                'product_id'=>0,
            );
            return $this->PropertyModel->insert($data);
        }

        public function saveDetail($data){
            //print_r($data);exit;

            foreach($data as $k=>$v){
                //保存商品属性
                if( $this->util->isChinese($k)){
                    $new_data=array();
                    $new_data['name']=$k;
                    $new_data['detail']=$v;
                    $new_data['status']=1;
                    $new_data['product_id']=$data['product_id'];
                    $this->PropertyModel->insert($new_data);
                    //保存detail
                }
                //保存商品详情
                if($k=='pro_detail'){
                    $new_data['name']='商品详情';
                    $new_data['detail']=$v;
                    $new_data['status']=1;
                    $new_data['product_id']=$data['product_id'];
                    $this->PropertyModel->insert($new_data);
                }
                //保存banner主图
                if($k=='img'){
                    //$img_id=array();
                    foreach($v as $name=>$pic){
                        $url=array(
                            'url'=>empty($pic)?'':$pic,
                            'from'=>1,
                            'time_create'=>time(),
                        );

                        $img_id=$this->image->insert($url);
                        $pro_img=array(
                            'img_id'=>$img_id,
                            'product_id'=>$data['product_id'],
                            'time_create'=>time(),
                        );
                        $this->product_image->insert($pro_img);
                    }


                }
                //保存主图
                if($k=='main_img_url'){
                    $main_id=$this->image->insert(array('url'=>empty($v)?'':$v));
                }
            }
            //保存商品信息
            $datas=array(
                'name' =>$data['name'],
                'product_id' =>intval($data['product_id']),
                'price' =>$data['price'],
                'main_img_url'=>$data['main_img_url'],
                'stock' => $data['stock'],
                'summary'=>$data['summary'],
                'category_id' =>intval($data['category_id']),
                'img_id'=>isset($main_id)?$main_id:'',
            );
            $pro_id['product_id']=$data['product_id'];
            return $this->pro_model->update($pro_id,$datas);


        }


        public function getPropertyByPropertyId($data){
            $property_id=intval($data['property_id']);
            return $this->PropertyModel->getPropertyByPropertyId($property_id);
        }

        public function updateProperty($data){
               $id['property_id']=intval($data['property_id']);
                $new_data['name']=$data['name'];
                $new_data['detail']=$data['detail'];
                $new_data['status']=1;
                $new_data ['product_id']=0;
            return $this->PropertyModel->update($id,$new_data);

        }

        public function delProperty($data){
            $id['property_id']=intval($data['property_id']);
            $new_data['status']=-1;
            return $this->PropertyModel->update($id,$new_data);

        }

        public function  getAllProperties($data=array()){
            $pid=isset($data['product_id'])?$data['product_id']:0;
            return $this->PropertyModel->getPropertiesByProductId($pid);
        }

       public function getBannerImgs($data){
            $product_id=intval($data['product_id']);
            return $this->product_image->getProductBannerImgsByProductId($product_id);
       }

       public function delBannerImgByImgId($data){
            $img_id['img_id']=intval($data['img_id']);
            $status['status']=-1;
           return $this->product_image->update($img_id,$status);

       }


       public function changeProductStatus($status){
           $pro['product_id']=intval($status['product_id']);
           $sta['status']=intval($status['status']);
           $res=$this->pro_model->update($pro,$sta);
           return $res;
       }

}