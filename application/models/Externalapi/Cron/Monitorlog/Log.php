<?php
/**
 * Created by PhpStorm.
 * User: l.gang06@yahoo.com
 * Date: 2017/5/8
 * Time: 15:21
 */
namespace Externalapi\Cron\Monitorlog;
abstract class LogModel
{
    protected $_filesMap = [];
    /**
     * @var \Api\Cache\Redis
     */
    protected $_cacheManager;
    protected $_runDate;
    const REDIS_KEY = 'monitorlog_';
    const REDIS_LIST_KEY = 'noticelog';
    protected $_appid = null;

    /**
     * @param $fileData  值为文件的一行数据
     * @return mixed
     */
    abstract function process($fileData);

    public function addListen($fileName)
    {
        if (is_array($fileName)) {
            foreach ($fileName as $file) {
                $this->addListen($file);
            }
            return false;
        }
        $this->_filesMap[$fileName] = [];
        if (!$this->_getFilemd5($fileName)) {
            echo "Warn：file is not exists" . PHP_EOL;
        }
    }


    public function getAppid()
    {
        if ($this->_appid === null) {
            $this->_appid = sha1(file_get_contents(APPLICATION_PATH_APP_REPOSITORY . '/app/appid'));
        }
        return $this->_appid;
    }

    public function start()
    {
        $this->_cacheManager = \Yaf\Registry::get('cacheManager');
        $this->_runDate = date('Ymd');
        $this->_initFileMap();
        while (true) {
            if ($this->_runDate != date('Ymd')) {
                break;
            }
            foreach ($this->_filesMap as $fileName => &$items) {
                $newMd5 = $this->_getFilemd5($fileName);
                if (!$newMd5 || $newMd5 == $items['md5']) {
                    continue;
                }
                $items['md5'] = $newMd5;
                $this->_procFileData($fileName, $items['len']);
                $this->_flushCache($fileName, $items['len']);
            }
            sleep(5);
        }
        return;
    }

    public function swooleProcessStart(\Swoole\Process $process)
    {
        $this->start();
        $process->exit(0);
    }

    protected function _procFileData($fileName, &$len, $readLine = 1000)
    {
        $fd = fopen($fileName, 'r+');
        if ($len < 0) {
            $len = filesize($fileName);
            fseek($fd, $len);
        } else {
            fseek($fd, $len);
        }
        $processData = [];
        $newLen = $len;
        while ($readLine-- > 0 && $data = fgets($fd)) {
            if (empty($data)) {
                continue;
            }
            $processData[] = $data;
            $newLen += strlen($data);
        }
        $len = $newLen;
        fclose($fd);
        if (!empty($processData)) {
            $this->process($processData);
        }
    }

    protected function _getUniqueKey($fileName)
    {
        return self::REDIS_KEY . $this->getAppid() . '_' . sha1(realpath($fileName));
    }
    protected function _flushCache($fileName, $len)
    {
        $key = $this->_getUniqueKey($fileName);
        $this->_cacheManager->set($key, $len);
        $this->_cacheManager->expire($key, 7 * 86400);
    }

    protected function _initFileMap()
    {
        foreach ($this->_filesMap as $fileName => $item) {
            $lastLen = $this->_cacheManager->get($this->_getUniqueKey($fileName));
            $this->_filesMap[$fileName]['md5'] = $this->_getFilemd5($fileName);
            if ($this->_filesMap[$fileName]['md5'] && !$lastLen) {
                $lastSize = filesize($fileName);
                $lastLen = $lastSize < 5000 ? 0 : -1;
            }
            $this->_filesMap[$fileName]['len'] = $lastLen;
        }
    }

    protected function _getFilemd5($fileName)
    {
        if (!file_exists($fileName)) {
            return false;
        }
        return md5_file($fileName);
    }

}
