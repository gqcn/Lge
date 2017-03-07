<?php
namespace Lge;

if(!defined('LGE')){
	exit('Include Permission Denied!');
}

/**
 * 生成对应数据库的数据字典.
 */
class Controller_DbDoc extends Controller_Base
{
    public function index()
    {
        $list    = array();
        $schemes = array('zbyl', 'iwshop');
        $db      = Instance::database('zbyl_db');
        foreach ($schemes as $scheme) {
            $result  = $db->query("SELECT * FROM `TABLES` WHERE `TABLE_SCHEMA`='{$scheme}'");
            while ($row = $db->fetchAssoc($result)) {
                $table   = array(
                    'name'    => $row['TABLE_NAME'],
                    'comment' => $row['TABLE_COMMENT'],
                    'columns' => array(),
                );
                $result2 = $db->query("SELECT * FROM `COLUMNS` WHERE `TABLE_SCHEMA`='{$scheme}' AND `TABLE_NAME`='{$row['TABLE_NAME']}'");
                while ($row2 = $db->fetchAssoc($result2)) {
                    $table['columns'][] = array(
                        'name'    => $row2['COLUMN_NAME'],
                        'type'    => $row2['COLUMN_TYPE'],
                        'key'     => $row2['COLUMN_KEY'],
                        'extra'    => $row2['EXTRA'],
                        'default' => $row2['COLUMN_DEFAULT'],
                        'comment' => $row2['COLUMN_COMMENT'],
                    );
                }
                $list[] = $table;
            }
        }
        
        $content  = '';
        $content .= "<ol>\n";
        foreach ($list as $table) {
            $comment  = empty($table['comment']) ? '' : " ({$table['comment']})";
            $content .= "<li><h2>{$table['name']}{$comment}</h2></li>\n";
            $content .= "<table>";
            $content .= "<thead>";
            $content .= "<tr>";
            $content .= "<th style='width:150px;'>名称</th>";
            $content .= "<th style='width:150px;'>类型</th>";
            $content .= "<th style='width:80px;'>主键</th>";
            $content .= "<th style='width:100px;'>附加</th>";
            $content .= "<th style='width:50px;'>默认</th>";
            $content .= "<th style='width:260px;'>备注</th>";
            $content .= "</tr>";
            $content .= "</thead>";
            $content .= "<tbody>";
            foreach ($table['columns'] as $column) {
                $content .= "<tr>";
                $content .= "<td>&nbsp;{$column['name']}</td>";
                $content .= "<td>&nbsp;{$column['type']}</td>";
                $content .= "<td>&nbsp;{$column['key']}</td>";
                $content .= "<td>&nbsp;{$column['extra']}</td>";
                $content .= "<td>&nbsp;{$column['default']}</td>";
                $content .= "<td>&nbsp;{$column['comment']}</td>";
                $content .= "</tr>";
            }
            $content .= "</tbody>";
            $content .= "</table>";
        }
        $content .= "</ol>\n";
        $tplContent = file_get_contents(Core::$sysDir. 'template/dbdoc.tpl.htm');
        $content    = str_ireplace('{$content}', $content, $tplContent);
        // file_put_contents(ROOT_PATH.'cache/html/doc.html', $content);
        echo $content;
    }
}
