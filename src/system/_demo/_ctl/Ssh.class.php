<?php
namespace Lge;

if (!defined('LGE')) {
	exit('Include Permission Denied!');
}

class Controller_Ssh extends Controller_Base
{
    
    /**
     * 默认入口函数.
     *
     * @return void
     */
    public function index()
    {
        $ssh  = new Lib_Network_Ssh('127.0.0.1', 22, 'john', '8692651');
        //$ssh  = new Lib_Network_Ssh('johnx.cn', 8822, 'john', '8692651');
        $cmds = array(
            //array('php -a', true),
            //array('var_dump(1);'),
            // "rsync -avz '-e ssh -p 8822' /home/john/temp/shake john@127.0.0.1:/home/john/temp/box",
            array("rsync -aurvz -e 'ssh -p 8822' /home/john/temp john@johnx.cn:/home/john/temp", false, 10),
            array("yes", true, 10),
            array("8692651", true, 10),
            array("echo 1"),
        );
        $ssh->shellCmd($cmds);
        // $ssh->cmd("top");
        //$r = $ssh->cmd("rsync -az '-e ssh -p 22000' /home/john/Workspace/PhpMe/PhpMe_PICC john@211.149.238.12:/home/john/temp");
    }
}