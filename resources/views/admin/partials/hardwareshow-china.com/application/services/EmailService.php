<?php

class EmailService{

    protected $excel_data;

    public function __construct()
    {
        $this->excel_data = new PHPExcel();

    }

}
