<?php
namespace Lge;

if (!defined('LGE')) {
	exit('Include Permission Denied!');
}

/**
 * 调试封装类.
 * 
 * @author john
 *
 */
class Debugger
{
    /**
     * 获取详细的调试信息，注意有的信息必须要在调试开关(DEBUG=1)打开的情况下才能获取.
     * 
     * @return array
     */
    public static function getDetailedInfo()
    {
        $detailedInfo = array();
        
        // memory usage in MB
        $detailedInfo['memory_used'] = round(memory_get_usage(true)/1024/1024, 2).' MB';
        
        // global variables
        $detailedInfo['global_vars'] = $GLOBALS;

        // all data in DATA
        $detailedInfo['data_vars']   = Data::getAll();

        // template variables
        $detailedInfo['template_vars'] = Instance::template()->getVars();
        
        // all database sqls
        $queries       = array();
        $totalCostTime = 0;
        foreach ($detailedInfo['data_vars'] as $key => $obj) {
            if (strpos($key, '_OBJ_DATABASE_') !== false) {
                $result = $obj->getQueriedSqls();
                foreach ($result as $item) {
                    $queries[]      = $item;
                    $totalCostTime += $item['cost'];
                }
            }
        }
        $detailedInfo['database_info'] = array(
            'queries'   => $queries,
            'cost_time' => $totalCostTime,
        );
        
        return $detailedInfo;
    }
    
    /**
     * 展示详细的调试信息.
     * 
     * @return void
     */
    public static function showDetailedInfo()
    {
        print_r(self::getDetailedInfo());
    }
}
