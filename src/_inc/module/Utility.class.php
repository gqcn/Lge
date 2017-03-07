<?php
namespace Lge;

if(!defined('LGE')){
    exit('Include Permission Denied!');
}
/**
 * 工具模块。
 *
 */
class Module_Utility extends BaseModule
{
    /**
     * 获得对象的方法，请使用该方法获得对象.
     *
     * @return Module_Utility
     */
    public static function instance ()
    {
        return self::instanceInternal(__CLASS__);
    }

    /**
     * 将Excel文件内容转换为数组.
     * 
     * @param string $filePath 文件绝对路径.
     *
     * @return array
     */
    public function getArrayFromExcel($filePath)
    {
        if (!file_exists($filePath)) {
            return array();
        }
        require_once ROOT_PATH.'_inc/library/PHPExcel/PHPExcel.class.php';
        // 根据文件名后缀判断导出excel类型
        $excelType = $this->_getExcelTypeByFileName($filePath);
        $xlsReader = PHPExcel_IOFactory::createReader($excelType);
        if (method_exists($xlsReader, 'setInputEncoding')) {
            $xlsReader->setInputEncoding('GBK');
        }

        $xlsReader->setReadDataOnly(true);
        $xlsReader->setLoadSheetsOnly(true);
        $sheets  = $xlsReader->load($filePath);
        $sheet   = $sheets->getSheet(0)->toArray();
        return $sheet;
    }
    
    /**
     * 将数组保存为excel文件.
     * 
     * @param string $filePath 文件名.
     * @param array  $headArr  头部标题名称.
     * @param array  $data     数据数组.
     *
     * @return mixed 如果成功返回true，否则返回失败的错误信息.
     */
    public function arrayToExcel($filePath, array $headArr, array $data)
    {
        if (empty($data) || ! is_array($data)) {
            return "数据数组不能为空";
        }
        if (empty($filePath)) {
            return "文件路径不能为空";
        }
        require_once ROOT_PATH.'_inc/library/PHPExcel/PHPExcel.class.php';

        //创建新的PHPExcel对象
        $objPHPExcel = new PHPExcel();
        $objProps = $objPHPExcel->getProperties();
        
        //设置表头
        $key = ord("A");
        foreach ( $headArr as $v ) {
            $colum = chr($key);
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue($colum.'1', $v);
            // 宽度自适应(中文会有点问题)
            $objPHPExcel->getActiveSheet()->getColumnDimension($colum)->setAutoSize(true);
            $key += 1;
        }
        
        $column = 2;
        $objActSheet = $objPHPExcel->getActiveSheet();
        // 行写入
        foreach ( $data as $key => $rows ) {
            $span = ord("A");
            // 列写入
            foreach ($rows as $keyName => $value) {
                $j = chr($span);
                $objActSheet->setCellValue($j . $column, $value);
                $span ++;
            }
            $column ++;
        }

        // 根据文件名后缀判断导出excel类型
        $excelType = $this->_getExcelTypeByFileName($filePath);
        //重命名表
        $objPHPExcel->getActiveSheet()->setTitle('Simple');
        //设置活动单指数到第一个表,所以Excel打开这是第一个表
        $objPHPExcel->setActiveSheetIndex(0);
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, $excelType);
        $objWriter->save($filePath);
    }

    /**
     * 根据文件名后缀判断导出excel类型
     *
     * @param string $fileName 文件名或者文件路径.
     * @return string
     */
    private function _getExcelTypeByFileName($fileName)
    {
        $type = substr($fileName, strrpos($fileName, '.') + 1);
        $type = strtolower($type);
        switch ($type) {
            case 'xls':
                // 2003或以下版本
                $excelType = 'Excel5';
                break;
            case 'xlsx':
                $excelType = 'Excel2007';
                break;
            case 'csv':
                $excelType = 'CSV';
                break;
            default:
                $excelType = 'Excel5';
                break;
        }
        return $excelType;
    }
}