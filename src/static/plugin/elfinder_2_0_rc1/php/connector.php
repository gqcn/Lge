<?php
include __DIR__.'/../../../../_cfg/const.inc.php';
include FRAME_PATH.'common.inc.php';
if (!sessionStarted()) {
    session_start();
}

if (empty($_SESSION['user']['uid'])) {
	header('Content-Type: text/html;charset=utf-8');
    echo '请先登陆后再进行操作！';
    exit();
}

set_time_limit(0);
ini_set('max_file_uploads',           50);
ini_set('php.internal_encoding', 'UTF-8');
ini_set('mbstring.func_overload',     2);

include_once __DIR__.DIRECTORY_SEPARATOR.'elFinderConnector.class.php';
include_once __DIR__.DIRECTORY_SEPARATOR.'elFinder.class.php';
include_once __DIR__.DIRECTORY_SEPARATOR.'elFinderVolumeDriver.class.php';
include_once __DIR__.DIRECTORY_SEPARATOR.'elFinderVolumeLocalFileSystem.class.php';

/**
 * Simple function to demonstrate how to control file access using "accessControl" callback.
 * This method will disable accessing files/folders starting from  '.' (dot)
 *
 * @param  string  $attr  attribute name (read|write|locked|hidden)
 * @param  string  $path  file path relative to volume root directory started with directory separator
 * @return bool|null
 **/
function access($attr, $path, $data, $volume) {
    // 缩略图隐藏
    if ((basename($path) == '.tmb' || preg_match("/\.(png|jpg|jpeg|gif)\.(\d+)x(\d+)\.(png|jpg|jpeg|gif)/i", $path)) && $attr == 'hidden') {
        return true;
    } else {
        return null;
    }
}

/**
 * 名字验证，主要用在上传以及重命名.
 * 
 * @param string $name 名字.
 * 
 * @return boolean
 */
function nameValidator($name) {
    // 不能命名为php文件
    $suffix = substr($name, strrpos($name, '.') + 1);
    if (strcasecmp($suffix, 'php') == 0) {
        return false;
    } else {
        return true;
    }
}

// 根据权限判断目录
$dirPath   = ROOT_PATH."upload/";
$urlPrefix = "/upload/";

// 如果目录不存在，则创建
if (!file_exists($dirPath)) {
	@mkdir($dirPath, 0777, true);
}

$opts    = array(
	'locale' => 'zh_CN.UTF-8',
	/*
	'bind'   => array(
		 'upload duplicate paste edit changed rm' => 'logger'
	),
	*/
	'debug' => false,
	'roots' => array(
		array(
			'driver'        => 'LocalFileSystem',
			'path'          => $dirPath,
			'startPath'     => '',
			'URL'           => $urlPrefix,
			// 'alias'      => 'File system',
			'mimeDetect'    => 'internal',
			'tmbPath'       => '.tmb',
			'utf8fix'       => true,
			'tmbCrop'       => false,
			'tmbBgColor'    => 'transparent',
			'accessControl' => 'access',
		    // 名字校验函数
		    'acceptedName'  => 'nameValidator',
		    // 禁止执行的命令
		    'disabled'      => array('archive', 'extract', 'help', 'resize'),
			// 'uploadDeny' => array('application', 'text/xml')
		),
	),
);


header('Access-Control-Allow-Origin: *');
$connector = new elFinderConnector(new elFinder($opts), true);
$connector->run();
