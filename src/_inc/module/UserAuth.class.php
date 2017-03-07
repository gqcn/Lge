<?php
namespace Lge;

if(!defined('LGE')){
    exit('Include Permission Denied!');
}
/**
 * 用户权限管理模型。
 *
 */
class Module_UserAuth extends BaseModule
{
    // 单进程下的用户组权限保存
    private $_userGroupAuths = array();
    // 单进程下的用户组Key保存
    private $_userGroupKeys  = array();

    /**
     * 获得实例.
     *
     * @return Module_UserAuth
     */
    public static function instance()
    {
        return self::instanceInternal(__CLASS__);
    }

    /**
     * 检查用户组是否有key的权限.
     *
     * @param string $userKey 用户组key.
     * @param mixed  $gid     用户组ID.
     *
     * @return bool
     */
    public function checkAuthByGid($userKey, $gid)
    {
        if ($gid === '') {
            return false;
        }

        if (empty($this->_userGroupKeys[$gid]) && $gid !== '') {
            $this->_userGroupKeys[$gid] = Instance::table('user_group')->getValue('group_key', array('id=?', $gid));
        }
        $key = isset($this->_userGroupKeys[$gid]) ? $this->_userGroupKeys[$gid] : '';
        switch ($key) {
            case 'super_admin':
                $result = true;
                break;

            default:
                $auths   = $this->getGroupAuths($gid);
                $userKey = $this->formatKey($userKey);
                $result  = isset($auths[$userKey]);
                break;
        }
        return $result;
    }

    /**
     * 获取设置的权限值.
     *
     * @param string  $userKey 权限key.
     * @param integer $gid     用户组ID.
     *
     * @return null|string
     */
    public function getAuthValueByGid($userKey, $gid)
    {
        if ($gid === '') {
            return null;
        }
        $value   = null;
        $checked = $this->checkAuthByGid($userKey, $gid);
        if ($checked) {
            $auths   = $this->getGroupAuths($gid);
            $userKey = $this->formatKey($userKey);
            $value   = $auths[$userKey];
        }
        return $value;
    }

    /**
     * 检查key是否一致.
     *
     * @param string $userKey
     * @param string $authKey
     *
     * @return bool
     */
    public function checkKey($userKey, $authKey)
    {
        $userKey = $this->formatKey($userKey);
        $authKey = $this->formatKey($authKey);
        $result  = (strcasecmp($userKey, $authKey) == 0);
        return $result;
    }

    /**
     * 检查key是否在权限列表中.
     *
     * @param string $userKey
     * @param array  $auths
     *
     * @return bool
     */
    public function checkAuthByAuths($userKey, array $auths)
    {
        $userKey = $this->formatKey($userKey);
        return isset($auths[$userKey]);
    }

    /**
     * 根据用户组ID获取权限.
     *
     * @param integer $gid 用户组ID.
     *
     * @return array
     */
    public function getGroupAuths($gid)
    {
        if (empty($this->_userGroupAuths[$gid])) {
            $auths     = array();
            $condition = array('gid' => $gid);
            $result    = Instance::table('user_group_auth')->getAll('`key`,`value`', $condition);
            foreach ($result as $v) {
                $key         = $this->formatKey($v['key']);
                $auths[$key] = $v['value'];
            }
            $this->_userGroupAuths[$gid] = $auths;
        }

        return $this->_userGroupAuths[$gid];
    }

    /**
     * 获得所有分站控制器方法数组列表.
     *
     * @param array   $auths
     * @param integer $pId
     *
     * @return array
     */
    public function getAuthTreeListBySystemCtl(array $auths, $pId = 0)
    {
        $tree  = new Lib_Tree($auths, array(
            'id'        => 'id',
            'parent_id' => 'pid',
            'name'      => 'key',
        ));
        return $tree->get_tree($pId, '$spacer $key');
    }

    /**
     * 获得所有分站控制器方法数组树形列表.
     *
     * @param string $specifiedSystem 指定获取的子系统名称(如果没有指定那么将会获取整个子系统列表的数据).
     *
     * @return array
     */
    public function getAuthListBySystemCtl($specifiedSystem = '')
    {
        $config = Config::get();
        $auths  = array();
        $sysDir = ROOT_PATH.'system/';
        $dirs   = scandir($sysDir);
        $index  = 0;
        foreach ($dirs as $dir) {
            if ($dir == '.' || $dir == '..') {
                continue;
            }
            if (!empty($specifiedSystem) && $specifiedSystem != $dir) {
                continue;
            }

            $system  = $dir;
            $id      = ++$index;
            $sysId   = $id;
            $auths[$id] = array(
                'id'     => $id,
                'pid'    => 0,
                'system' => $system,
                'key'    => "System:{$system}",
                'name'   => isset($config['System'][$system]['name']) ? $config['System'][$system]['name'] : "System:{$system}",
                'value'  => 'system',
            );

            $parsedArray = $this->_parseCtrlDir($sysDir.$dir.'/_ctl/');
            foreach ($parsedArray as $array) {
                if (!empty($array['acts'])) {
                    $id    = ++$index;
                    $ctlId = $id;
                    $auths[$id] = array(
                        'id'     => $id,
                        'pid'    => $sysId,
                        'system' => $system,
                        'key'    => $array['ctl'][0],
                        'name'   => $array['ctl'][1],
                        'value'  => 'ctl',
                    );
                    foreach ($array['acts'] as $item) {
                        $id  = ++$index;
                        $auths[$id] = array(
                            'id'     => $id,
                            'pid'    => $ctlId,
                            'system' => $system,
                            'key'    => "{$array['ctl'][0]}/{$item[0]}",
                            'name'   => $item[1],
                            'value'  => 'act',
                        );
                    }
                }
            }
        }
        return $auths;
    }

    /**
     * 获取对应控制器中方法的描述(注释).
     *
     * @param string $act     方法名称.
     * @param string $ctlFile 控制器文件绝对路径.
     *
     * @return string
     */
    public function getActBriefByAct($act, $ctlFile)
    {
        $result = '';
        $parsed = $this->_parseCtlFile($ctlFile, true);
        if (!empty($parsed)) {
            foreach ($parsed['acts'] as $item) {
                if (strcasecmp($act, $item[0]) == 0) {
                    $result = "{$parsed['ctl'][1]}::{$item[1]}";
                    break;
                }
            }
        }
        return $result;
    }

    /**
     * 处理key的格式.
     * @param $key
     * @return mixed|string
     */
    public function formatKey($key)
    {
        $key = str_replace(array('-', '.', '_', '/'), '#', $key);
        $key = strtolower($key);
        return $key;
    }

    /**
     * 解析控制器目录.
     * @param $dir
     * @param array $result
     * @return array
     */
    private function _parseCtrlDir($dir, $result = array())
    {
        $files = scandir($dir);
        foreach ($files as $k => $file) {
            if ($file == '.' || $file == '..') {
                continue;
            }
            $path = $dir.$file;
            if (is_dir($path)) {
                $result = $this->_parseCtrlDir($path.'/', $result);
            } else {
                $parsed = $this->_parseCtlFile($path);
                if (!empty($parsed)) {
                    $result[] = $parsed;
                }
            }
        }
        return $result;
    }

    /**
     * 解析控制器类文件.
     *
     * @param string  $path       文件路径.
     * @param boolean $forceParse 强制解析该控制器类文件(默认必须要父类是带Auth权限控制才解析).
     *
     * @return array
     */
    private function _parseCtlFile($path, $forceParse = false)
    {
        $parsed  = array();
        $content = file_get_contents($path);
        $tokens  = token_get_all($content);
        $index   = 0;
        $value   = null;
        $result  = $this->_getNextTokenValue('T_CLASS', $tokens, $index);
        if (!empty($result)) {
            list($index, $_)         = $result;
            list($index, $ctlName)   = $this->_getNextTokenValue('T_STRING', $tokens, $index);
            list($_, $extendCtlName) = $this->_getNextTokenValue('T_STRING', $tokens, $index + 1);
            // 通过关键字'Auth'来判断该控制器是否需要权限控制
            if (!$forceParse && strpos($extendCtlName, 'Auth') === false) {
                return array();
            }
            list($_, $ctlComment)    = $this->_getNextTokenValue('T_COMMENT,T_DOC_COMMENT', $tokens, $index - 10);
            $ctlName    = str_ireplace('Controller_', '', $ctlName);
            $ctlComment = $this->_trimComment($ctlComment);
            if (empty($ctlComment)) {
                $ctlComment = $ctlName;
            }
            $parsed = array(
                'ctl'  => array($ctlName, $ctlComment),
                'acts' => array(),
            );
            while (true) {
                list($index, $_)          = $this->_getNextTokenValue('T_PUBLIC', $tokens, $index);
                list($index, $act)        = $this->_getNextTokenValue('T_STRING', $tokens, $index);
                list($_,     $actComment) = $this->_getNextTokenValue('T_COMMENT,T_DOC_COMMENT', $tokens, $index - 10);
                if ($index !== null) {
                    if ($act[0] != '_') {
                        $actComment = $this->_trimComment($actComment);
                        if (empty($actComment)) {
                            $actComment = $act;
                        }
                        $parsed['acts'][] = array($act, $actComment);
                    }
                } else {
                    break;
                }
            }
        }

        return $parsed;
    }

    /**
     * 格式化注释并返回.
     *
     * @param string $comment 注释.
     *
     * @return string
     */
    private function _trimComment($comment)
    {
        $array      = explode("\n", $comment);
        $newComment = '';
        foreach ($array as $k => $v) {
            $v = trim($v, " \t/\\*\r\n.");
            if (empty($v) || (isset($v[0]) && $v[0] == '@')) {
                unset($array[$k]);
            } else {
                $newComment .= $v;
            }
        }
        return $newComment;
    }

    /**
     * 获取下一个满足条件的token.
     *
     * @param string  $tokenName 多个token名字以半角的','号分隔.
     * @param array   $tokens    token数组.
     * @param integer $offset    数组索引.
     * @return array
     */
    private function _getNextTokenValue($tokenName, $tokens, $offset = 0)
    {
        $i          = $offset;
        $count      = count($tokens);
        $result     = array(null, null);
        $tokenNames = explode(',', $tokenName);
        while ($offset !== null && $i < $count) {
            if (isset($tokens[$i])) {
                $token = $tokens[$i];
                $name  = is_array($token) ? token_name($token[0]) : null;
                $data  = is_array($token) ? $token[1] : $token;
                // var_dump("{$name}: {$data}");
                if (in_array($name, $tokenNames)) {
                    $result = array($i, $data);
                    break;
                }
            }
            ++$i;
        }
        return $result;
    }

}
