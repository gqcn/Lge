<?php
if(!defined('PhpMe')){
	exit('Include Permission Denied!');
}

/**
 * 系统设置
 */
class Controller_Setting extends BaseControllerAdminAuth
{

    /**
     * 文件管理
     */
    public function fileManager()
    {
        $this->assigns(array(
            'mainTpl' => 'setting/file-manager',
        ));
        $this->display();
    }

    /**
     * 执行设置.
     */
    public function set()
    {
        if (Lib_Request::isRequestMethodPost()) {
            $k = Lib_Request::getPost('k', '');
            $v = Lib_Request::getPost('v', '');
            if (!empty($v)) {
                Module_Setting::Instance()->set($k, $v);
            }
            $this->addMessage('设置保存完成', 'success');
            Lib_Redirecter::redirectExit();
        }
    }

    /**
     * 区域管理
     */
    public function geoinfo()
    {
        //分别获取到省市区县乡镇的数据
        $addressProvince = Instance::table('geoinfo_province')->getAll(); //省
        $addressCity = Instance::table('geoinfo_city')->getAll(); //市
        $countyTable = "geoinfo_county AS gco";
        $countyTable .= " LEFT JOIN geoinfo_city AS gci ON gco.city_id = gci.id";
        $countyFiled = "gco.*,gci.province_id";
        $addressCounty = Instance::table($countyTable)->getAll($countyFiled); //区县
        $townTable = "geoinfo_town AS gto";
        $townTable .= " LEFT JOIN geoinfo_county AS gco ON gco.id = gto.county_id";
        $townTable .= " LEFT JOIN geoinfo_city AS gci ON gci.id = gco.city_id";
        $townFiled = "gto.*,gco.city_id,gci.province_id";
        $addressTown = Instance::table($townTable)->getAll($townFiled); //乡镇
        //获取乡镇级地址
        $townMap = array();
        foreach ($addressTown as $key => $item) {
            $townMap[$item['county_id']][] = array(
                'id' => "{$item['province_id']}_{$item['city_id']}_{$item['county_id']}_{$item['id']}",
                'name' => $item['name'],
                'pId' => "{$item['province_id']}_{$item['city_id']}_{$item['county_id']}_0",
                'real_id' => $item['id'],
                'checked' => $item['extra'] == 1 ? true : false,
                'key' => 'town@' . $item['id']
            );
        }
        //获取区县级地址
        $countyMap = array();
        foreach ($addressCounty as $key => $item) {
            $countyMap[$item['city_id']][] = array(
                'id' => "{$item['province_id']}_{$item['city_id']}_{$item['id']}_0",
                'name' => $item['name'],
                'pId' => "{$item['province_id']}_{$item['city_id']}_0_0",
                'real_id' => $item['id'],
                'checked' => $item['extra'] == 1 ? true : false,
                'key' => 'county@' . $item['id']
            );
        }
        //获取市级地址
        $cityMap = array();
        foreach ($addressCity as $key => $item) {
            $cityMap[$item['province_id']][] = array(
                'id' => "{$item['province_id']}_{$item['id']}_0_0",
                'name' => $item['name'],
                'pId' => "{$item['province_id']}_0_0_0",
                'real_id' => $item['id'],
                'checked' => $item['extra'] == 1 ? true : false,
                'key' => 'city@' . $item['id']
            );
        }
        //获取省级地址，并组装结果地址数组
        $treeArray = array();
        foreach ($addressProvince as $key => $item) {
            $treeArray[] = array(
                'id' => "{$item['id']}_0_0_0",
                'name' => $item['name'],
                'pId' => 0,
                'real_id' => $item['id'],
                'checked' => $item['extra'] == 1 ? true : false,
                'key' => 'province@' . $item['id']
            );
            //多层级地区数据组合
            if (isset($cityMap[$item['id']])) {
                $cities = $cityMap[$item['id']];
                foreach ($cities as $k => $v) {
                    $treeArray[] = $v;
                    if (isset($countyMap[$v['real_id']])) {
                        $countys = $countyMap[$v['real_id']];
                        foreach ($countys as $k2 => $v2) {
                            $treeArray[] = $v2;
                            if (isset($townMap[$v2['real_id']])) {
                                $towns = $townMap[$v2['real_id']];
                                foreach ($towns as $k3 => $v3) {
                                    $treeArray[] = $v3;
                                }
                            }
                        }
                    }
                }
            }
        }
        unset($addressProvince, $addressCity, $addressCounty, $addressTown, $cityMap, $countyMap, $townMap);
        $this->assigns(array(
            'data' => array(),
            'treeJson' => json_encode($treeArray),
            'mainTpl' => 'setting/geoinfo',
        ));
        $this->display();
    }

    /**
     * 区域管理 - 修改区域状态
     */
    public function editExtra()
    {
        $category = 'admin/geoinfo/' . __FUNCTION__;
        $keyStr = Lib_Request::getPost('keys');
        if (!empty($keyStr)) {
            $keyArray = explode(',', $keyStr);
            foreach ($keyArray as $key => $item) {
                $value = explode('@', $item);
                switch ($value[0]) {
                    case 'province':
                        $provinceId[] = $value[1];
                        break;
                    case 'city':
                        $cityId[] = $value[1];
                        break;
                    case 'county':
                        $countyId[] = $value[1];
                        break;
                    case 'town':
                        $townId[] = $value[1];
                        break;
                }
            }
        }
        $data['extra']  = 1;
        $ndata['extra'] = 0;
        if (!empty($provinceId) && count($provinceId) > 0) {
            //更新操作
            $provinceStr = implode(',', $provinceId);
            $provinceUpdate1 = Instance::table('geoinfo_province')->update($data, "id IN({$provinceStr})");
            $provinceUpdate2 = Instance::table('geoinfo_province')->update($ndata, "id NOT IN({$provinceStr})");
            if(true !== $provinceUpdate1 || true !== $provinceUpdate2){
                Logger::log('modify geoinfo_province extra: ' . $provinceStr, $category);
                $this->addMessage("省级信息的地区开放修改失败", 'error');
            }
        }
        if (!empty($cityId) && count($cityId) > 0) {
            //更新操作
            $cityStr = implode(',', $cityId);
            $cityUpdate1 = Instance::table('geoinfo_city')->update($data, "id IN({$cityStr})");
            $cityUpdate2 = Instance::table('geoinfo_city')->update($ndata, "id NOT IN({$cityStr})");
            if(true !== $cityUpdate1 || true !== $cityUpdate2){
                Logger::log('modify geoinfo_province extra: ' . $cityStr, $category);
                $this->addMessage("市级信息的地区开放修改失败", 'error');
            }
        }
        if (!empty($countyId) && count($countyId) > 0) {
            //更新操作
            $countyStr = implode(',', $countyId);
            $countyUpdate1 = Instance::table('geoinfo_county')->update($data, "id IN({$countyStr})");
            $countyUpdate2 = Instance::table('geoinfo_county')->update($ndata, "id NOT IN({$countyStr})");
            if(true !== $countyUpdate1 || true !== $countyUpdate2){
                Logger::log('modify geoinfo_province extra: ' . $countyStr, $category);
                $this->addMessage("市级信息的地区开放修改失败", 'error');
            }
        }
        if (!empty($townId) && count($townId) > 0) {
            //更新操作
            $townStr = implode(',', $townId);
            $townUpdate1 = Instance::table('geoinfo_town')->update($data, "id IN({$townStr})");
            $townUpdate2 = Instance::table('geoinfo_town')->update($ndata, "id NOT IN({$townStr})");
            if(true !== $townUpdate1 || true !== $townUpdate2){
                Logger::log('modify geoinfo_province extra: ' . $townStr, $category);
                $this->addMessage("市级信息的地区开放修改失败", 'error');
            }
        }
        Lib_Redirecter::redirectExit();
    }
}
