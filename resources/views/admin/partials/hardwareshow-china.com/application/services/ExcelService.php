<?php
/**
 * Created by PhpStorm.
 * User: Chris_Chiang
 * Date: 2018/7/26
 * Time: 12:11
 */
class ExcelService
{

    protected $excel_data;

    public function __construct()
    {
        $this->excel_data = new PHPExcel();

    }


    public function getExcelDataByFile($res)
    {
        //传入Excel文件路径
        try{

            //建立reader对象
            $objReader = PHPExcel_IOFactory::createReader('excel2007');
            $objPHPExcel = $objReader->load($res);
            $sheet = $objPHPExcel->getSheet();
            $highestRow = $sheet->getHighestDataRow(); // 取得总行数
            //设置表格的列；//默认4列，具体根据实际业务需求进行修改
            $columns = array('A', 'B', 'C', 'D',);
            $arr_result = array();
            $dealer_element = array();

        for ($j = 1; $j <= $highestRow; $j++) {
            for ($k = 0; $k < count($columns); $k++) {
                $value = $objPHPExcel->getActiveSheet()->getCell($columns[$k] . $j)->getValue();//这个就是获取每个单元格的值

                $value = trim($value);
                if (empty($value)) {
                    $value = NULL;
                }
                $dealer_element[$k] = $value;
            }

            $arr_result[$j] = $dealer_element;
            //返回读取的数据
        }

          //返回二维数组；
          return $arr_result;
        }catch(Exception $e){
            $e->getMessage();

        }
    }
}
