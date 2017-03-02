<?php


if (!defined('PhpMe')) {
    exit('Include Permission Denied!');
}

/**
 * FTP操作类
 *
 */
class Lib_Network_Ftp
{
    private $_ftp;
    private $_host;
    private $_port;
    private $_passport;
    private $_password;
    
    public function __construct($host, $port = 21, $passport = null, $password = null)
    {
        $this->_port = $port;
        $this->_host = $host;
        $this->_passport = $passport;
        $this->_password = $password;
    }
    
    public function __destruct()
    {
        $this->close();
    }
    
    /**
     * 获取FTP服务器上的一个文件，并保存到本地路径
     * @param  string $ftpFilePath
     * @param  string $localPath
     * @return bool
     */
    public function fetch($ftpFilePath, $localPath)
    {
        if(!$this->_ftp){
            $this->_init();
        }
        $return = false;
        if(ftp_get($this->_ftp, $localPath, $ftpFilePath, FTP_BINARY)){
            $return = true;
        }
        return $return;
    }
    
    /**
     * FTP上传本地文件。
     * 使用FTP函数实现。
     *
     * @param  string  $ftpUrl 远程FTP服务器文件地址
     * @param  string  $filePath 本地文件地址
     * @param  string  $passport
     * @param  string  $password
     * @return boolean
     */
    public function upload($ftpUrl, $filePath, $passport = null, $password = null, $port = 21)
    {
        if(!file_exists($filePath)){
            return false;
        }
        if(!$this->_ftp){
            $this->_init();
        }
        
        //解析FTP的URL
        $urlInfo = parse_url($ftpUrl);
        //文件路径及文件名称
        $dirName  = dirname($urlInfo['path']);
        $baseName = basename($urlInfo['path']);
        //递归创建目录
        $dirPath = null;
        $dirs    = explode('/', str_ireplace('\\', '/', $dirName));
        foreach ($dirs as $dir){
            if($dir){
                $dirPath = $dirPath ? $dirPath.'/'.$dir : $dir;
                @ftp_mkdir($this->_ftp, $dirPath);
            }
        }
        //上传文件
        return @ftp_put($this->_ftp, $dirPath.'/'.$baseName, $filePath, FTP_BINARY);
    }
    
    /**
     * 删除FTP上的一个文件
     *
     * @param  string  $ftpFilePath FTP上的文件路径，例如: dir1/dir2/file.type
     * @return boolean
     */
    public function remove($ftpFilePath)
    {
        if(!$this->_ftp){
            $this->_init();
        }
        return @ftp_delete($this->_ftp, $ftpFilePath);
    }
    
    /**
     * 关闭连接
     * 
     * @return boolean
     */
    public function close()
    {
        if($this->_ftp){
           return @ftp_close($this->_ftp);
        }
        return true;
    }
    
    /**
     * 初始化FTP连接
     *
     * @return resource
     */
    private function _init()
    {
        //解析FTP的URL并连接登录
        $conn = @ftp_connect($this->_host, $this->_port);
        if(!$conn) {
            return false;
        }
        if($this->_passport && $this->_password){
            $login = @ftp_login($conn, $this->_passport, $this->_password);
            if(!$login) {
                return false;
            }
        }
        $this->_ftp = $conn;
    }
}
