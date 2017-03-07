<?php
namespace Lge;

if (!defined('LGE')) {
	exit('Include Permission Denied!');
}

/**
 * 该文件是自定义的一些文件系统操作函数
 * 
 * 
 */
class Lib_FileSys
{
	/**
	 * 复制目录，将$sourcePath目录里面的文件照原样复制到$targetPath,注意目录名应该相同，即需要创建目录
	 *
	 * @param  string $sourcePath
	 * @param  string $targetPath
	 * @return boolean
	 */
	public static function copyDir($sourcePath, $targetPath)
	{
	    if(!file_exists($sourcePath)){
	        return false;
	    }
	    if(!file_exists($targetPath)){
	        mkdir($targetPath, 0777, true);
	    }
		foreach (scandir($sourcePath) as $file){
			if($file=='.' || $file=='..'){
			    continue;
			}
			if(is_dir($sourcePath.'/'.$file)){
			    self::copyDir($sourcePath.'/'.$file, $targetPath.'/'.$file);
			}else{
			    copy($sourcePath.'/'.$file, $targetPath.'/'.$file);
			}
		}
		
		return true;
	}

	/**
	 * 递归删除目录(目录里面的文件也会被删除！)
	 *
	 * @param  string $sourcePath
	 * @return boolean
	 */
	public static function deleteDir($sourcePath)
	{
		if (file_exists($sourcePath) && !@rmdir($sourcePath)) {
		    // 删除里面的文件
			foreach (scandir($sourcePath) as $file){
				if($file == '.' || $file == '..'){
				    continue;
				}
				$filePath = $sourcePath.'/'.$file;
				if(is_dir($filePath)){
				    @self::deleteDir($filePath);
				}else{
				    @unlink($filePath);
				}
			}
			//再删除目录
			@rmdir($sourcePath);
		}
		return true;
	}

	/**
	 * 清除目录内容(与deleteDir不同的是不会删除自身目录)
	 *
	 * @param  string $sourcePath
	 * @return boolean
	 */
	public static function clearDir($sourcePath)
	{
	    //删除里面的文件
		foreach (scandir($sourcePath) as $file){
			if($file=='.' || $file=='..'){
			    continue;
			}
			$filePath = $sourcePath.'/'.$file;
			if(is_dir($filePath)){
			    @self::deleteDir($filePath);
			}else{
			    @unlink($filePath);
			}
		}
		return true;
	}

	/**
	 * 获得目录的大小
	 *
	 * @param  string $sourcePath
	 * @return int
	 */
	public static function getDirSize($sourcePath)
	{
		static $dirSize = 0;
		foreach (scandir($sourcePath) as $file){
			if($file=='.' || $file=='..'){
			    continue;
			}
			$filePath = $sourcePath.'/'.$file;
			if(is_dir($filePath)){
			    self::getDirSize($filePath);
			}else{
			    $dirSize += filesize($filePath);
			}
		}
		return $dirSize;
	}

	/**
	 * 格式化文件大小
	 *
	 * @param  int $fileSize
	 * @return string
	 */
	public static function formatSize($fileSize)
	{
		if($fileSize < 1024){
			$fileSize = round($fileSize/1024, 2);
			$fileSize .= ' KB';
		}else{
			$fileSize = round($fileSize/1024, 2);
			if($fileSize < 1024){
				$fileSize .= ' KB';
			}else{
				$fileSize = round($fileSize/1024, 2);
				if($fileSize < 1024){
					$fileSize .= ' MB';
				}else{
					$fileSize = round($fileSize/1024, 2);
					$fileSize .= ' GB';
				}
			}
		}
		return $fileSize;
	}

	/**
	 * 获得文件的大小并格式化单位返回字符串
	 *
	 * @param  string $filePath
	 * @return string
	 */
	public static function getFileSize($filePath)
	{
		return self::formatSize(filesize($filePath));
	}

	/**
	 * 检查文件是否为文本文件(可编辑, 常用文件名后缀)
	 * 根据文件后缀名判断
	 *
	 * @param  string $filePath
	 * @return boolean
	 */
	public static function isEditable($filePath)
	{
		switch (self::getFileType($filePath)){
			case 'txt':	case 'ini':	case 'htm':	case 'html':case 'js':case 'css':case 'php':
			case 'asp':	case 'jsp': case 'htaccess':
				return true;
				break;

			default:
				return false;
				break;
		}
	}

	/**
	 * 判断文件类型是否为压缩文件(常用文件名后缀)
	 * 根据文件后缀名判断
	 *
	 * @param  string $filePath
	 * @return boolean
	 */
	public static function isCompress($filePath)
	{
		switch(self::getFileType($filePath)){
			case '7z':
			case 'rar':
			case 'zip':
			case 'bz2':
			case 'tar':
			case 'rpm':
			    return true;
			    break;
			default:
			    return false;
			    break;
		}
	}

	/**
	 * 判断文件类型是否为视频文件(常用文件名后缀)
	 * 根据文件后缀名判断
	 *
	 * @param  string $filePath
	 * @return boolean
	 */
	public static function isVideo($filePath)
	{
		switch(self::getFileType($filePath)){
			case 'swf':
			case 'rmvb':
			case 'flv':
			case 'wmv':
			case 'avi':
			case 'rm':
			    return true;
			    break;
			default:
			    return false;
			    break;
		}
	}

	/**
	 * 判断文件类型是否为音频文件(常用文件名后缀)
	 * 根据文件后缀名判断
	 *
	 * @param  string $filePath
	 * @return boolean
	 */
	public static function isAudio($filePath)
	{
		switch(self::getFileType($filePath)){
			case 'm4a':
			case 'mp3':
			case 'wma':
			case 'mid':
			case 'ogg':
			case 'flv':
			case 'mp4':
			case 'wav':
				return true;
				break;
			default:
				return false;
				break;
		}
	}

	/**
	 * 判断文件类型是否为图象文件(常用文件名后缀)
	 * 根据文件后缀名判断
	 *
	 * @param  string $filePath
	 * @return boolean
	 */
	public static function isImage($filePath)
	{
		switch(self::getFileType($filePath)){
			case 'gif':
			case 'bmp':
			case 'jpg':
			case 'jpeg':
			case 'png':
			    return true;
			    break;
			default:
			    return false;
			    break;
		}
	}

	/**
	 * 判断文件类型是否为普通文档文件(常用文件名后缀)
	 * 根据文件后缀名判断
	 *
	 * @param  string $filePath
	 * @return boolean
	 */
	public static function isDocument($filePath)
	{
		switch(self::getFileType($filePath)){
			case 'doc':
			case 'txt':
			case 'ini':
			case 'chm':
			case 'reg':
			    return true;
			    break;
			default:
			    return false;
			    break;
		}
	}

	/**
	 * 获取文件类型字符串.
	 *
	 * @param $filePath 文件路径或者文件名.
	 * @return string
	 */
	public static function getFileType($filePath)
	{
		$type = substr($filePath, strrpos($filePath, '.') + 1);
		$type = strtolower($type);
		return $type;
	}

    /**
     * 上传文件(到本地服务器)
     *
     * @param  array  $FILE           单个文件数组
     * @param  string $dirPath        文件目录
     * @param  string $fileName       文件名称，为空则按照时间自动命名
     * @param  bool   $replaceIfExist 如果有同名文件则覆盖，否则自动重命名文件
     * @return string | false
     */
	public static function uploadfile($FILE, $dirPath, $fileName = null, $replaceIfExist = false)
	{
	    $type = strrchr($FILE['name'], '.');
		if (empty($fileName)) {
			$name = date('YmdHis');
		} else {
		    $name = substr($fileName, 0, strrpos($fileName, '.'));
		}
		$fileName   = $name.$type;
		$uploadFile = $dirPath.$fileName;
		if (file_exists($uploadFile) && $replaceIfExist == false) {
			$i = 1;
			do {
				$fileName   = $name.'_'.$i++.$type;
				$uploadFile = $dirPath.$fileName;
			} while(file_exists($uploadFile));
		}
		if (@move_uploaded_file($FILE['tmp_name'], $uploadFile)) {
		    return $fileName;
		} else {
		    return false;
		}
	}

    /**
     * 将数组导出到csv文件，并提供下载.
     *
     * @param array  $list         数组数据.
     * @param string $glue         分隔符号.
     * @param string $filePath     保存的文件路径.
     * @param string $downloadName 如果需要下载的话，这个是下载的文件名，如果不下载，那么保留为空.
     * @param string $encoding     内容需要转换的编码.
     *
     * @return void
     */
	public static function exportDataToCsv(array $list,
                                           $glue         = ',',
                                           $filePath     = '',
                                           $downloadName = '',
                                           $encoding     = 'gbk'
    )
	{
        $content = '';
        foreach ($list as $item) {
            if (is_array($item)) {
				// 自动转换为Excel可识别的字符串格式
                $content .= '"'.implode('"'.$glue.'="', $item).'"';
            } else {
                $content .= '="'.$item.'"';
            }
            $content .= PHP_EOL;
        }
        // 编码转换
        if (!empty($encoding)) {
            $content = mb_convert_encoding($content, $encoding, 'utf-8');
        }
        // 保存到文件
        if (!empty($filePath)) {
            file_put_contents($filePath, $content);
        }
        // 下载文件
        if (!empty($downloadName)) {
            // 浏览器显示下载，使用readfile前先判断并关闭缓冲，防止内存占用问题
            if (ob_get_level()) {
                ob_end_clean();
            }
            header("Content-Type: application/vnd.ms-excel;charset=utf-8");
            header("Content-Disposition: attachment;filename=\"{$downloadName}.csv\"");
            header("Cache-Control: max-age=0");
            if (!empty($filePath)) {
                readfile($filePath);
            } else {
                echo $content;
            }
            unset($content);
            exit();
        }
	}
}