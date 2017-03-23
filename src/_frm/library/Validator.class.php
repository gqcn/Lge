<?php
namespace Lge;

if (!defined('LGE')) {
    exit('Include Permission Denied!');
}

/**
 * 表单/数据校验类.
 * $rules = array(
 *     'true_name' => 'required',
 *     'user_pass' => array('required|length:6,24', array('required' => '用户名不能为空', 'length' => '用户名长度为6到24个字符')),
 *
 * );
 *
 * @author John
 */
class Lib_Validator
{
    public static $defaultMessages = array(
        'required'   => '字段不能为空',
        'email'      => '邮箱地址格式不正确',
        'ip'         => 'IP地址格式不正确',
        'mac'        => 'MAC地址格式不正确',
        'url'        => 'URL地址格式不正确',
        'length'     => '字段长度为:min到:max个字符',
        'min_length' => '字段最小长度为:min',
        'max_length' => '字段最大长度为:max',
        'between'    => '字段大小为:min到:max',
        'min'        => '字段最小值为:min',
        'max'        => '字段最大值为:max',
        'integer'    => '字段应当为整数',
        'float'      => '字段应当为浮点数',
        'boolean'    => '字段应当为布尔值',
    );

    /**
     * 根据规则验证数组，如果返回值为空那么表示满足规则，否则返回值为错误信息数组.
     *
     * @param array $data  数据数组.
     * @param array $rules 规则数组.
     * @return array
     */
    public static function check(array $data, array $rules) {
        $result = array();
        foreach ($rules as $key => $rule) {
            if (isset($data[$key])) {
                $r = self::checkRule($data[$key], $rule);
                if (!empty($r)) {
                    $result[$key] = $r;
                }
            }
        }
        return $result;
    }

    /**
     * 根据单条规则验证数值，如果返回值为空那么表示满足规则，否则返回值为错误信息数组.
     *
     * @param mixed $value 数值.
     * @param mixed $rule  规则.
     * @return array
     */
    private static function checkRule($value, $rule) {
        $result   = array();
        $messages = array();
        if (is_array($rule)) {
            $ruleString = $rule[0];
            $messages   = $rule[1];
        } else {
            $ruleString = $rule;
        }
        $ruleArray = explode('|', $ruleString);
        foreach ($ruleArray as $ruleItem) {
            $ruleMatch   = true;
            $ruleMessage = '';
            $tmpArray    = explode(':', $ruleItem);
            $ruleName    = isset($tmpArray[0]) ? $tmpArray[0] : null;
            $ruleAttr    = isset($tmpArray[1]) ? $tmpArray[1] : null;
            switch ($ruleName) {
                // 必须字段
                case 'required':
                    $ruleMatch = !empty($value);
                    break;

                // 长度范围
                case 'length':
                    $length   = mb_strlen($value, 'utf-8');
                    $tmpArray = explode(',', $ruleAttr);
                    $min      = isset($tmpArray[0]) ? $tmpArray[0] : null;
                    $max      = isset($tmpArray[1]) ? $tmpArray[1] : null;
                    if ($length < $min || $length > $max) {
                        $ruleMatch = false;
                        if (isset($messages[$ruleName])) {
                            $ruleMessage = $messages[$ruleName];
                        } else {
                            $ruleMessage = self::$defaultMessages[$ruleName];
                            $ruleMessage = str_ireplace(array(':min', ':max'), array($min, $max), $ruleMessage);
                        }
                    }
                    break;

                // 最小长度
                case 'min_length':
                    $length    = mb_strlen($value, 'utf-8');
                    $minLength = $ruleAttr;
                    if ($length < $minLength) {
                        $ruleMatch = false;
                        if (isset($messages[$ruleName])) {
                            $ruleMessage = $messages[$ruleName];
                        } else {
                            $ruleMessage = self::$defaultMessages[$ruleName];
                            $ruleMessage = str_ireplace(':min', $minLength, $ruleMessage);
                        }
                    }
                    break;

                // 最大长度
                case 'max_length':
                    $length    = mb_strlen($value, 'utf-8');
                    $maxLength = $ruleAttr;
                    if ($length > $maxLength) {
                        $ruleMatch = false;
                        if (isset($messages[$ruleName])) {
                            $ruleMessage = $messages[$ruleName];
                        } else {
                            $ruleMessage = self::$defaultMessages[$ruleName];
                            $ruleMessage = str_ireplace(':max', $maxLength, $ruleMessage);
                        }
                    }
                    break;

                // 大小范围
                case 'between':
                    $tmpArray = explode(',', $ruleAttr);
                    $min      = isset($tmpArray[0]) ? $tmpArray[0] : null;
                    $max      = isset($tmpArray[1]) ? $tmpArray[1] : null;
                    if ($value < $min || $value > $max) {
                        $ruleMatch = false;
                        if (isset($messages[$ruleName])) {
                            $ruleMessage = $messages[$ruleName];
                        } else {
                            $ruleMessage = self::$defaultMessages[$ruleName];
                            $ruleMessage = str_ireplace(array(':min', ':max'), array($min, $max), $ruleMessage);
                        }
                    }
                    break;

                // 最小值
                case 'min':
                    $min = $ruleAttr;
                    if ($value < $min) {
                        $ruleMatch = false;
                        if (isset($messages[$ruleName])) {
                            $ruleMessage = $messages[$ruleName];
                        } else {
                            $ruleMessage = self::$defaultMessages[$ruleName];
                            $ruleMessage = str_ireplace(':min', $min, $ruleMessage);
                        }
                    }
                    break;

                // 最大值
                case 'max':
                    $max = $ruleAttr;
                    if ($value > $max) {
                        $ruleMatch = false;
                        if (isset($messages[$ruleName])) {
                            $ruleMessage = $messages[$ruleName];
                        } else {
                            $ruleMessage = self::$defaultMessages[$ruleName];
                            $ruleMessage = str_ireplace(':max', $max, $ruleMessage);
                        }
                    }
                    break;

                // 整数
                case 'integer': $ruleMatch = filter_var($value, FILTER_VALIDATE_INT)     !== false; break;
                // 小数
                case 'float':   $ruleMatch = filter_var($value, FILTER_VALIDATE_FLOAT)   !== false; break;
                // 布尔值(1,true,on,yes:true | 0,false,off,no,"":false)
                case 'boolean': $ruleMatch = filter_var($value, FILTER_VALIDATE_BOOLEAN) !== false; break;
                // 邮件
                case 'email':   $ruleMatch = filter_var($value, FILTER_VALIDATE_EMAIL)   !== false; break;
                // URL
                case 'url':     $ruleMatch = filter_var($value, FILTER_VALIDATE_URL)     !== false; break;
                // IP
                case 'ip':      $ruleMatch = filter_var($value, FILTER_VALIDATE_IP)      !== false; break;
                // MAC地址
                case 'mac':     $ruleMatch = filter_var($value, FILTER_VALIDATE_MAC)     !== false; break;
            }
            // 错误信息判断
            if (!empty($ruleMessage)) {
                $result[$ruleName] = $ruleMessage;
            } elseif (!$ruleMatch) {
                $result[$ruleName] = isset($messages[$ruleName]) ? $messages[$ruleName] : self::$defaultMessages[$ruleName];
            }
        }
        return $result;
    }
}